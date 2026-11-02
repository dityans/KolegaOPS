<?php
// controllers/AnalisisProfitController.php
// Alias Controller Analisis Profit yang mewarisi LaporanController
// Menjamin rute 'controller=analisisProfit' atau 'controller=analisis_profit' berjalan mulus

require_once BASE_PATH . '/controllers/LaporanController.php';

class AnalisisProfitController extends LaporanController
{
    public function index(): void
    {
        $this->profit();
    }
}
