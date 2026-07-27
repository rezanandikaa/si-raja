# P3KE Lebak

Sistem Informasi Analisa P3KE (Percepatan Penghapusan Kemiskinan Ekstrem) di Kabupaten Lebak, Provinsi Banten - Indonesia

## Panduan Setting Server
- Menggunakan versi PHP => 8.2, mengikuti panduan dari dokumentasi Laravel 10.x
- PHP FPM buka ```$ sudo nano /etc/php/8.2/fpm/php.ini``` tambahkan untuk ```memory_limit = 512M```, ```post_max_size = 512M```, ```upload_max_filesize = 512M```, ```max_execution_time = 1800``` lalu restart ```sudo service php8.2-fpm restart```
- Nginx buka ```$ sudo nano /etc/nginx/nginx.conf``` tambahkan ```client_max_body_size 500M;``` lalu restart ```sudo service nginx restart```
- MariaDB buka ```$ sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf``` tambahkan ```max_allowed_packet = 1G``` lalu restart ```sudo service mariadb restart```

## Panduan Instalasi

- Clone dari github 
- Install dengan composer ```$ composer install```
- Setting database dll di file .env (kalau tidak ada copy dari .env.example)
- Jalankan perintah artisan ```php artisan migrate --seed```
- Tambahkan vendor dengan cara ```php artisan vendor:publish``` dan pilih beberapa yang akan di override contoh ```Mews\Captcha\CaptchaServiceProvider```
- Tambahkan ```ADMIN_LOGIN=administrator``` di file ```.env```
- Pastikan ```RouteServiceProvider.php``` menjadi ```public const HOME = '/administrator';``` mengikuti value dari ```ADMIN_LOGIN``` pada .env ini berguna untuk meredirect login / authenticated.
- Enjoy

## Panduan Lainnya
- Karena import dengan queue maka server wajib running ```php artisan queue:work```
- Karena file diupload dulu, maka server diatur scheduling jalankan perintah ```php artisan baduyengine:import``` setiap 1 jam sekali. Proses ini sengaja diletakan di server agar tidak membenani client.
- Setelah itu server wajib menjalankan ```php artisan baduyengine:sync``` untuk singkronisasi ke database master P3KE.

## Panduan CronJob
- Untuk dapatkan statistik tambahkan perintah ```php artisan baduyengine:statistic``` pada cronjob per 1 jam / 6 jam sekali

## Ucapan Terima Kasih
- Enjoy using App.
