<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut berisi pesan standar yang digunakan oleh kelas
    | validasi. Beberapa aturan memiliki beberapa versi seperti aturan ukuran.
    | Silakan sesuaikan setiap pesan di sini sesuai kebutuhan aplikasi.
    |
    */

    'accepted' => 'Isian :attribute harus diterima.',
    'accepted_if' => 'Isian :attribute harus diterima ketika :other bernilai :value.',
    'active_url' => 'Isian :attribute harus berupa URL yang valid.',
    'after' => 'Isian :attribute harus berupa tanggal/waktu setelah :date.',
    'after_or_equal' => 'Isian :attribute harus berupa tanggal/waktu setelah atau sama dengan :date.',
    'alpha' => 'Isian :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Isian :attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num' => 'Isian :attribute hanya boleh berisi huruf dan angka.',
    'any_of' => 'Isian :attribute tidak valid.',
    'array' => 'Isian :attribute harus berupa sebuah array.',
    'ascii' => 'Isian :attribute hanya boleh berisi karakter alfanumerik dan simbol single-byte.',
    'before' => 'Isian :attribute harus berupa tanggal/waktu sebelum :date.',
    'before_or_equal' => 'Isian :attribute harus berupa tanggal/waktu sebelum atau sama dengan :date.',
    'between' => [
        'array' => 'Isian :attribute harus memiliki antara :min dan :max item.',
        'file' => 'Isian :attribute harus berukuran antara :min dan :max kilobita.',
        'numeric' => 'Isian :attribute harus bernilai antara :min dan :max.',
        'string' => 'Isian :attribute harus berisi antara :min dan :max karakter.',
    ],
    'boolean' => 'Isian :attribute harus bernilai true atau false.',
    'can' => 'Isian :attribute berisi nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => 'Isian :attribute tidak memiliki nilai yang dibutuhkan.',
    'current_password' => 'Kata sandi salah.',
    'date' => 'Isian :attribute harus berupa tanggal yang valid.',
    'date_equals' => 'Isian :attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => 'Isian :attribute harus sesuai format :format.',
    'decimal' => 'Isian :attribute harus memiliki :decimal angka desimal.',
    'declined' => 'Isian :attribute harus ditolak.',
    'declined_if' => 'Isian :attribute harus ditolak ketika :other bernilai :value.',
    'different' => 'Isian :attribute dan :other harus berbeda.',
    'digits' => 'Isian :attribute harus terdiri dari :digits digit angka.',
    'digits_between' => 'Isian :attribute harus terdiri dari :min hingga :max digit angka.',
    'dimensions' => 'Isian :attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => 'Isian :attribute memiliki nilai yang duplikat.',
    'doesnt_contain' => 'Isian :attribute tidak boleh berisi salah satu dari: :values.',
    'doesnt_end_with' => 'Isian :attribute tidak boleh diakhiri dengan salah satu dari: :values.',
    'doesnt_start_with' => 'Isian :attribute tidak boleh diawali dengan salah satu dari: :values.',
    'email' => 'Isian :attribute harus berupa alamat surel/email yang valid.',
    'encoding' => 'Isian :attribute harus menggunakan pengkodean :encoding.',
    'ends_with' => 'Isian :attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => 'Nilai :attribute yang dipilih tidak valid.',
    'exists' => 'Nilai :attribute yang dipilih tidak ditemukan.',
    'extensions' => 'Isian :attribute harus memiliki salah satu ekstensi berikut: :values.',
    'file' => 'Isian :attribute harus berupa sebuah berkas/file.',
    'filled' => 'Isian :attribute harus memiliki nilai.',
    'gt' => [
        'array' => 'Isian :attribute harus memiliki lebih dari :value item.',
        'file' => 'Isian :attribute harus lebih besar dari :value kilobita.',
        'numeric' => 'Isian :attribute harus lebih besar dari :value.',
        'string' => 'Isian :attribute harus memiliki lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => 'Isian :attribute harus memiliki :value item atau lebih.',
        'file' => 'Isian :attribute harus lebih besar dari atau sama dengan :value kilobita.',
        'numeric' => 'Isian :attribute harus lebih besar dari atau sama dengan :value.',
        'string' => 'Isian :attribute harus memiliki minimal :value karakter.',
    ],
    'hex_color' => 'Isian :attribute harus berupa kode warna heksadesimal yang valid.',
    'image' => 'Isian :attribute harus berupa gambar (foto).',
    'in' => 'Isian :attribute yang dipilih tidak valid.',
    'in_array' => 'Isian :attribute harus ada dalam :other.',
    'integer' => 'Isian :attribute harus berupa bilangan bulat.',
    'ip' => 'Isian :attribute harus berupa alamat IP yang valid.',
    'ipv4' => 'Isian :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => 'Isian :attribute harus berupa alamat IPv6 yang valid.',
    'json' => 'Isian :attribute harus berupa string JSON yang valid.',
    'list' => 'Isian :attribute harus berupa sebuah daftar/list.',
    'lowercase' => 'Isian :attribute harus berupa huruf kecil.',
    'lt' => [
        'array' => 'Isian :attribute harus memiliki kurang dari :value item.',
        'file' => 'Isian :attribute harus kurang dari :value kilobita.',
        'numeric' => 'Isian :attribute harus kurang dari :value.',
        'string' => 'Isian :attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => 'Isian :attribute tidak boleh memiliki lebih dari :value item.',
        'file' => 'Isian :attribute harus kurang dari atau sama dengan :value kilobita.',
        'numeric' => 'Isian :attribute harus kurang dari atau sama dengan :value.',
        'string' => 'Isian :attribute harus memiliki maksimal :value karakter.',
    ],
    'mac_address' => 'Isian :attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => 'Isian :attribute tidak boleh memiliki lebih dari :max item.',
        'file' => 'Ukuran :attribute tidak boleh lebih dari :max kilobita.',
        'numeric' => 'Nilai :attribute tidak boleh lebih dari :max.',
        'string' => 'Isian :attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits' => 'Isian :attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes' => 'Isian :attribute harus berupa berkas dengan tipe: :values.',
    'mimetypes' => 'Isian :attribute harus berupa berkas dengan tipe: :values.',
    'min' => [
        'array' => 'Isian :attribute harus memiliki minimal :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobita.',
        'numeric' => 'Nilai :attribute minimal :min.',
        'string' => 'Isian :attribute minimal :min karakter.',
    ],
    'min_digits' => 'Isian :attribute harus memiliki minimal :min digit.',
    'missing' => 'Isian :attribute harus tidak ada.',
    'missing_if' => 'Isian :attribute harus tidak ada ketika :other bernilai :value.',
    'missing_unless' => 'Isian :attribute harus tidak ada kecuali :other bernilai :value.',
    'missing_with' => 'Isian :attribute harus tidak ada ketika ada :values.',
    'missing_with_all' => 'Isian :attribute harus tidak ada ketika semua :values ada.',
    'multiple_of' => 'Isian :attribute harus merupakan kelipatan dari :value.',
    'not_in' => 'Isian :attribute yang dipilih tidak valid.',
    'not_regex' => 'Format isian :attribute tidak valid.',
    'numeric' => 'Isian :attribute harus berupa angka.',
    'password' => [
        'letters' => 'Isian :attribute harus mengandung setidaknya satu huruf.',
        'mixed' => 'Isian :attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => 'Isian :attribute harus mengandung setidaknya satu angka.',
        'symbols' => 'Isian :attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => 'Isian :attribute yang dimasukkan terindikasi bocor dalam kebocoran data. Silakan pilih :attribute yang lain.',
    ],
    'present' => 'Isian :attribute harus ada.',
    'present_if' => 'Isian :attribute harus ada ketika :other bernilai :value.',
    'present_unless' => 'Isian :attribute harus ada kecuali :other bernilai :value.',
    'present_with' => 'Isian :attribute harus ada ketika ada :values.',
    'present_with_all' => 'Isian :attribute harus ada ketika semua :values ada.',
    'prohibited' => 'Isian :attribute dilarang.',
    'prohibited_if' => 'Isian :attribute dilarang ketika :other bernilai :value.',
    'prohibited_if_accepted' => 'Isian :attribute dilarang ketika :other diterima.',
    'prohibited_if_declined' => 'Isian :attribute dilarang ketika :other ditolak.',
    'prohibited_unless' => 'Isian :attribute dilarang kecuali :other ada di :values.',
    'prohibits' => 'Isian :attribute melarang :other untuk ada.',
    'regex' => 'Format isian :attribute tidak valid.',
    'required' => 'Isian :attribute wajib diisi.',
    'required_array_keys' => 'Isian :attribute harus berisi entri untuk: :values.',
    'required_if' => 'Isian :attribute wajib diisi ketika :other bernilai :value.',
    'required_if_accepted' => 'Isian :attribute wajib diisi ketika :other diterima.',
    'required_if_declined' => 'Isian :attribute wajib diisi ketika :other ditolak.',
    'required_unless' => 'Isian :attribute wajib diisi kecuali :other ada dalam :values.',
    'required_with' => 'Isian :attribute wajib diisi jika terdapat :values.',
    'required_with_all' => 'Isian :attribute wajib diisi jika terdapat :values.',
    'required_without' => 'Isian :attribute wajib diisi jika tidak terdapat :values.',
    'required_without_all' => 'Isian :attribute wajib diisi jika sama sekali tidak ada :values.',
    'same' => 'Isian :attribute harus sama dengan :other.',
    'size' => [
        'array' => 'Isian :attribute harus mengandung :size item.',
        'file' => 'Ukuran :attribute harus berukuran :size kilobita.',
        'numeric' => 'Isian :attribute harus berukuran :size.',
        'string' => 'Isian :attribute harus berukuran :size karakter.',
    ],
    'starts_with' => 'Isian :attribute harus diawali dengan salah satu dari: :values.',
    'string' => 'Isian :attribute harus berupa string teks.',
    'timezone' => 'Isian :attribute harus berupa zona waktu yang valid.',
    'unique' => 'Isian :attribute sudah digunakan sebelumnya.',
    'uploaded' => 'Berkas :attribute gagal diunggah.',
    'uppercase' => 'Isian :attribute harus berupa huruf besar.',
    'url' => 'Isian :attribute harus berupa URL yang valid.',
    'ulid' => 'Isian :attribute harus berupa ULID yang valid.',
    'uuid' => 'Isian :attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi Kustom
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan pesan validasi kustom untuk atribut
    | menggunakan konvensi "attribute.rule" untuk menamai baris.
    |
    */

    'custom' => [
        'jam_selesai' => [
            'after' => 'Jam selesai harus lebih besar (setelah) dari :date.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atribut Kustom
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut digunakan untuk menukar 'placeholder' atribut kami
    | dengan sesuatu yang lebih ramah pembaca seperti "Alamat Surel" daripada
    | "email". Ini membantu membuat pesan kami lebih ekspresif.
    |
    */

    'attributes' => [
        'nama_lengkap' => 'nama lengkap',
        'nis' => 'NIS',
        'nip' => 'NIP',
        'kelas_id' => 'kelas',
        'mapel_id' => 'mata pelajaran',
        'guru_id' => 'guru',
        'siswa_id' => 'siswa',
        'jenis_kelamin' => 'jenis kelamin',
        'tanggal_lahir' => 'tanggal lahir',
        'tahun_masuk' => 'tahun masuk',
        'alamat' => 'alamat',
        'foto' => 'foto',
        'status' => 'status',
        'hari' => 'hari',
        'jam_ke' => 'jam ke',
        'jam_mulai' => 'jam mulai',
        'jam_selesai' => 'jam selesai',
        'ruangan' => 'ruangan',
        'tahun_ajaran' => 'tahun ajaran',
        'semester' => 'semester',
        'nilai_tugas' => 'nilai tugas',
        'nilai_uts' => 'nilai UTS',
        'nilai_uas' => 'nilai UAS',
        'password' => 'kata sandi',
        'email' => 'email',
        'username' => 'nama pengguna',
        'role' => 'peran',
        'no_hp' => 'nomor HP',
        'no_wa' => 'nomor WhatsApp',
    ],

];
