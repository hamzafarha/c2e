<?php

// src/Command/TestOllamaCommand.php
namespace App\Command;

use App\Service\OllamaService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestOllamaCommand extends Command
{
    protected static $defaultName = 'app:test-ollama';
    private OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testEmail = "Rapport Iperius Backup\nDate: 2024-05-01 22:00:00\nStatut: Succès\nTaille: 45.2 GB\nFichiers: 1245";
        
        try {
            $result = $this->ollamaService->parseEmailWithOllama($testEmail);
            $output->writeln(json_encode($result, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Erreur: '.$e->getMessage().'</error>');
            return Command::FAILURE;
        }
    }
}