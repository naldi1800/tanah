Saya sedang mengembangkan Sistem Pendukung Keputusan Penentuan Lahan Budidaya Tanaman Porang menggunakan Laravel 11.

Metode yang digunakan adalah Analytical Hierarchy Process (AHP).

Namun AHP hanya digunakan untuk menentukan bobot kriteria, sedangkan penilaian alternatif dilakukan berdasarkan data hasil pengukuran lapangan.

Jangan menggunakan AHP murni untuk membandingkan alternatif secara subjektif.

Tujuan Sistem

Menentukan jalan yang paling direkomendasikan sebagai lokasi budidaya tanaman porang.

Alternatif berupa nama jalan, bukan setiap data tanah.

Contoh data alamat:

Jln. Anggrek No 10
Jln. Anggrek No 14
Jln. Anggrek No 21

Semuanya harus dikelompokkan menjadi

Jln. Anggrek

Nomor rumah diabaikan.

Setiap jalan mewakili beberapa data tanah.

Model

Semua data berasal dari

Model Tanah

Field yang digunakan

jenis_tanah
ph
kelembapan
suhu
ketinggian
alamat
Kriteria

Gunakan tepat lima kriteria

Jenis Tanah
pH Tanah
Kelembapan
Suhu
Ketinggian
Matriks Perbandingan Kriteria

Gunakan matriks berikut

Kriteria	JT	PH	KL	SH	KT
Jenis Tanah	1	5	5	7	7
pH	1/5	1	3	5	5
Kelembapan	1/5	1/3	1	3	3
Suhu	1/7	1/5	1/3	1	2
Ketinggian	1/7	1/5	1/3	1/2	1

Alasan bobot

Jenis Tanah merupakan faktor paling penting.
pH merupakan faktor kedua.
Kelembapan merupakan faktor ketiga.
Suhu merupakan faktor keempat.
Ketinggian merupakan faktor pendukung.

Gunakan matriks tersebut untuk menghitung bobot AHP.

Tahapan Perhitungan AHP

Program wajib menampilkan seluruh proses berikut.

Tahap 1

Menampilkan Matriks Perbandingan Kriteria.

Tahap 2

Menghitung jumlah setiap kolom.

Tahap 3

Normalisasi Matriks.

Tahap 4

Menghitung Priority Vector.

Tahap 5

Menampilkan Bobot Akhir Kriteria.

Tahap 6

Menghitung λmax.

Tahap 7

Menghitung CI

CI = (λmax - n)/(n-1)
Tahap 8

Menghitung CR

CR = CI / RI

Gunakan

RI = 1.12

karena jumlah kriteria adalah lima.

Tahap 9

Menampilkan status

Konsisten

atau

Tidak Konsisten
Pengolahan Alternatif

JANGAN membuat matriks perbandingan alternatif.

Alternatif dinilai menggunakan data lapangan.

Langkah 1

Kelompokkan seluruh data berdasarkan nama jalan.

Contoh

Jln. Anggrek

memiliki

15 data tanah
Langkah 2

Hitung statistik setiap jalan.

Program harus menghasilkan

|Jalan|Jumlah Titik|Jenis Dominan|Rata pH|Rata Kelembapan|Rata Suhu|Rata Ketinggian|

Jenis tanah menggunakan modus.

Yang lain menggunakan rata-rata.

Langkah 3

Konversi setiap nilai menjadi skor.

Jenis Tanah
Jenis	Skor
Lempung Berpasir	5
Lempung	4
Lempung Liat	3
Liat	2
Pasir	1
pH
Rentang	Skor
6.0–7.0	5
5.5–5.9 atau 7.1–7.5	4
5.0–5.4 atau 7.6–8.0	3
4.5–4.9	2
lainnya	1
Kelembapan
Rentang	Skor
60–70	5
55–59 atau 71–75	4
50–54 atau 76–80	3
45–49	2
lainnya	1
Suhu
Rentang	Skor
25–30	5
23–24 atau 31–32	4
21–22 atau 33–34	3
19–20	2
lainnya	1
Ketinggian
Rentang	Skor
700–2000 mdpl	5
500–699	4
300–499	3
100–299	2
lainnya	1
Langkah 4

Hitung nilai akhir setiap jalan menggunakan bobot AHP.

Gunakan rumus

Nilai Akhir

=

(Bobot Jenis Tanah × Skor Jenis Tanah)

+

(Bobot pH × Skor pH)

+

(Bobot Kelembapan × Skor Kelembapan)

+

(Bobot Suhu × Skor Suhu)

+

(Bobot Ketinggian × Skor Ketinggian)
Langkah 5

Urutkan nilai dari terbesar ke terkecil.

Output Halaman

Gunakan Bootstrap.

Setiap proses ditampilkan pada Card terpisah.

Urutan Card

Data Tanah
Hasil Pengelompokan Jalan
Statistik Tiap Jalan
Matriks Perbandingan Kriteria
Jumlah Kolom
Matriks Normalisasi
Priority Vector
Bobot Kriteria
λmax
CI
CR
Status Konsistensi
Tabel Skor Alternatif
Perhitungan Nilai Akhir
Ranking Jalan

Struktur Program

Gunakan prinsip Laravel 11 yang rapi.

Pisahkan menjadi:

Controller
Service AHP
Helper jika diperlukan
Blade

Jangan menaruh seluruh logika pada Controller.

Seluruh rumus AHP dibuat dinamis sehingga jika jumlah kriteria berubah, proses perhitungan tetap berjalan.

Semua tabel perhitungan harus ditampilkan secara lengkap, termasuk angka pada setiap langkah, agar pengguna dapat memverifikasi hasil perhitungan dan memahami proses AHP dari awal hingga menghasilkan peringkat akhir.