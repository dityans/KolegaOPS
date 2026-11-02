<?php
// controllers/PegawaiController.php
// Alias Controller Pegawai yang mewarisi seluruh fungsi UserController
// Menjamin kompatibilitas rute URL 'controller=pegawai' dan 'controller=user'

require_once BASE_PATH . '/controllers/UserController.php';

class PegawaiController extends UserController
{
    // Mewarisi index(), simpan(), dan hapus() dari UserController
}
