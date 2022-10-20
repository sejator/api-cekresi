# API Cek Resi

Contoh Implementasi Curl Request Codeigniter 4 Untuk Mengecek No Resi.

## Persyaratan
 - Semua persyaratan mengacu ke dokumentasi codeigniter 4. [Dokumentasi](https://codeigniter.com/user_guide/intro/requirements.html)
 - Membutuhkan library HTML DOM Parser. [Dokumentasi](https://github.com/paquettg/php-html-parser/blob/master/README.md)

## Cara Install
 - Download project ini. `git clone https://github.com/sejator/api-cekresi.git`
 - Masuk ke direktori `cd api-cekresi`
 - Jalankan `composer update` untuk mendownload dependensinya.
 - Running aplikasi `php spark serve` kemudian buka urlnya `http://localhost:8080/`
 - Opsional untuk development buat file `.env` dengan perintah `php spark key:generate` kemudian edit bagian `#CI_ENVIRONMENT = production` menjadi `CI_ENVIRONMENT = development`

## Endpoint
Ganti parameter resi untuk melacak detail no resi.

`http://localhost:8080/tracking?kurir=jne&resi=8825112045716759`
