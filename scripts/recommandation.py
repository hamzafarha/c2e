import pandas as pd
from sqlalchemy import create_engine
from prophet import Prophet
from datetime import datetime
import json
import logging
import sys
import os


logging.getLogger('cmdstanpy').setLevel(logging.CRITICAL)
logging.getLogger('prophet').setLevel(logging.CRITICAL)
sys.stderr = open(os.devnull, 'w')

# --- 1. Connexion à la base MySQL via SQLAlchemy ---
engine = create_engine('mysql+mysqlconnector://root:@localhost/c2e')

# --- 2. Extraction des sorties ---
sortie_query = """
SELECT
    a.id AS article_id,
    a.refart AS reference,
    s.datesortie AS date_sortie,
    s.quantite AS quantite_sortie
FROM
    article a
JOIN
    sortiestock s ON s.idart_id = a.id
"""
df = pd.read_sql(sortie_query, engine)
df['date_sortie'] = pd.to_datetime(df['date_sortie'])

# --- 3. Extraction des entrées pour calcul du stock ---
entree_query = '''
SELECT
    idart_id AS article_id,
    SUM(quantite) AS total_entree
FROM
    entreestock
GROUP BY idart_id
'''
entrees = pd.read_sql(entree_query, engine)

# --- 4. Somme des sorties par article ---
sorties = df.groupby('article_id')['quantite_sortie'].sum().reset_index(name='total_sortie')

# --- 5. Calcul du stock actuel ---
stock_df = pd.merge(entrees, sorties, on='article_id', how='outer').fillna(0)
stock_df['stock_actuel'] = stock_df['total_entree'] - stock_df['total_sortie']

# --- 6. Calculs et prédictions ---
results = []

for (article_id, reference), group in df.groupby(['article_id', 'reference']):
    group = group.sort_values('date_sortie')
    group['month'] = group['date_sortie'].dt.to_period('M')
    monthly = group.groupby('month')['quantite_sortie'].sum()
    conso_mensuelle = monthly.mean()

    prophet_df = group[['date_sortie', 'quantite_sortie']].rename(columns={'date_sortie': 'ds', 'quantite_sortie': 'y'})
    if len(prophet_df) > 1:
        model = Prophet(yearly_seasonality=False, daily_seasonality=False)
        model.fit(prophet_df)
        future = model.make_future_dataframe(periods=30)
        forecast = model.predict(future)
        forecast['yhat'] = forecast['yhat'].clip(lower=0)
        forecast['cumsum'] = forecast['yhat'].cumsum()
        stock_actuel = int(stock_df.loc[stock_df['article_id'] == article_id, 'stock_actuel'].values[0])
        rupture_row = forecast[forecast['cumsum'] >= stock_actuel].head(1)
        if not rupture_row.empty:
            date_rupture = rupture_row['ds'].values[0]
            date_rupture = pd.to_datetime(date_rupture)
            rupture_dans_jours = (date_rupture - datetime.now()).days
        else:
            rupture_dans_jours = None
    else:
        stock_actuel = int(stock_df.loc[stock_df['article_id'] == article_id, 'stock_actuel'].values[0])
        rupture_dans_jours = int(stock_actuel / conso_mensuelle * 30) if conso_mensuelle > 0 else None

    suggestion_commande = int(round(conso_mensuelle)) if conso_mensuelle > 0 else 0

    results.append({
        "article_id": int(article_id),
        "reference": reference,
        "conso_mensuelle": int(round(conso_mensuelle)),
        "stock_actuel": int(stock_actuel),
        "rupture_dans_jours": int(rupture_dans_jours) if rupture_dans_jours is not None else None,
        "suggestion_commande": int(suggestion_commande)
    })

# --- 7. Sortie JSON ---
print(json.dumps(results, indent=2, ensure_ascii=False)) 