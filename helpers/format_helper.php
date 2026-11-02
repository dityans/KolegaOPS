<?php
// helpers/format_helper.php
// Berisi fungsi-fungsi pembantu untuk format angka uang, tanggal, dan waktu

if (!function_exists('format_rupiah')) {
    /**
     * Memformat angka desimal/integer menjadi format mata uang Rupiah.
     * Contoh: 15000 -> "Rp 15.000"
     *
     * @param float|int|string|null $angka
     * @param bool $denganSimbol Menampilkan prefix 'Rp '
     * @return string
     */
    function format_rupiah($angka, bool $denganSimbol = true): string
    {
        $nilai = (float) ($angka ?? 0);
        $hasil = number_format($nilai, 0, ',', '.');
        return $denganSimbol ? 'Rp ' . $hasil : $hasil;
    }
}

if (!function_exists('format_tanggal')) {
    /**
     * Memformat tanggal YYYY-MM-DD menjadi format tanggal Indonesia.
     * Contoh: "2026-11-02" -> "2 November 2026"
     *
     * @param string|null $datetime Tanggal atau datetime
     * @param bool $singkat Menggunakan nama bulan singkatan (Jan, Feb, ...)
     * @return string
     */
    function format_tanggal(?string $datetime, bool $singkat = false): string
    {
        if (empty($datetime)) {
            return '-';
        }

        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return (string) $datetime;
        }

        $bulanPanjang = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $bulanSingkat = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
        ];

        $d = date('j', $timestamp);
        $m = (int) date('n', $timestamp);
        $y = date('Y', $timestamp);

        $daftarBulan = $singkat ? $bulanSingkat : $bulanPanjang;
        $namaBulan = $daftarBulan[$m] ?? '';

        return "{$d} {$namaBulan} {$y}";
    }
}

if (!function_exists('format_waktu')) {
    /**
     * Memformat jam menit dari string datetime.
     * Contoh: "2026-11-02 14:30:00" -> "14:30"
     *
     * @param string|null $datetime
     * @param bool $denganDetik
     * @return string
     */
    function format_waktu(?string $datetime, bool $denganDetik = false): string
    {
        if (empty($datetime)) {
            return '-';
        }

        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return (string) $datetime;
        }

        return date($denganDetik ? 'H:i:s' : 'H:i', $timestamp);
    }
}

if (!function_exists('format_tanggal_waktu')) {
    /**
     * Menggabungkan tanggal Indonesia dan jam.
     * Contoh: "2 November 2026, 14:30"
     *
     * @param string|null $datetime
     * @return string
     */
    function format_tanggal_waktu(?string $datetime): string
    {
        if (empty($datetime)) {
            return '-';
        }

        return format_tanggal($datetime) . ', ' . format_waktu($datetime);
    }
}
