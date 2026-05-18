<?php
// app/Console/Commands/ResetMonthlyLimitsCommand.php
namespace App\Console\Commands;

use App\Services\LimiteService;
use Illuminate\Console\Command;

class ResetMonthlyLimitsCommand extends Command
{
    protected $signature   = 'limits:reset';
    protected $description = 'Reset valor_gasto_atual de todos os limites (executar dia 01)';

    public function __construct(private LimiteService $limiteService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->limiteService->resetMonthly();
        $this->info('Limites resetados com sucesso.');
        return Command::SUCCESS;
    }
}
