import fs from 'fs';
import path from 'path';
import puppeteer from 'puppeteer';

const BASE_URL = process.env.BASE_URL || 'https://siakad.ghaibnet.co.id';
const GURU_EMAIL = process.env.GURU_EMAIL || 'guru17@siakadnuja.sch.id';
const GURU_PASSWORD = process.env.GURU_PASSWORD || 'password';
const ONLY_GURU = process.argv.includes('--guru-only') || process.argv.includes('--guru');
const SCREENSHOT_DIR = path.resolve(process.cwd(), 'screenshots');

if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

function getExecutablePath() {
    if (process.env.PUPPETEER_EXECUTABLE_PATH && fs.existsSync(process.env.PUPPETEER_EXECUTABLE_PATH)) {
        return process.env.PUPPETEER_EXECUTABLE_PATH;
    }

    const possiblePaths = [
        '/usr/bin/google-chrome',
        '/usr/bin/google-chrome-stable',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
        'C:\\Users\\g0str\\.cache\\puppeteer\\chrome\\win64-151.0.7922.47\\chrome-win64\\chrome.exe',
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe'
    ];

    for (const p of possiblePaths) {
        if (fs.existsSync(p)) {
            return p;
        }
    }
    return null;
}

async function takeScreenshots() {
    console.log('🚀 Starting screenshot generator...');
    console.log(`🌐 Target BASE_URL: ${BASE_URL}`);

    const execPath = getExecutablePath();
    const launchOptions = {
        headless: 'new',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1440,900'
        ]
    };

    if (execPath) {
        console.log(`📌 Using browser executable: ${execPath}`);
        launchOptions.executablePath = execPath;
    }

    const browser = await puppeteer.launch(launchOptions);
    const page = await browser.newPage();
    page.setDefaultNavigationTimeout(60000);
    page.setDefaultTimeout(60000);
    await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1 });

    // Daftar Halaman Guru
    const guruPagesToCapture = [
        { name: '41_guru_dashboard.png', path: '/dashboard' },
        { name: '42_guru_jadwal_index.png', path: '/jadwal' },
        { name: '43_guru_nilai_index.png', path: '/nilai' },
        { name: '44_guru_absensi_index.png', path: '/absensi' },
        { name: '45_guru_laporan_index.png', path: '/laporan' },
    ];

    if (ONLY_GURU) {
        console.log(`🔑 Direct Login as Guru (${GURU_EMAIL})...`);
        await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2', timeout: 60000 });
        await page.type('input[name="email"]', GURU_EMAIL);
        await page.type('input[name="password"]', GURU_PASSWORD);
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }),
            page.click('button[type="submit"]')
        ]);
        console.log('✅ Logged in as Guru successfully!');

        for (const p of guruPagesToCapture) {
            try {
                const url = `${BASE_URL}${p.path}`;
                console.log(`📸 Capturing Guru: ${p.name} (${p.path})`);
                await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });

                if (p.action) {
                    await p.action(page);
                }

                const targetPath = path.join(SCREENSHOT_DIR, p.name);
                await page.screenshot({ path: targetPath, fullPage: false });
            } catch (err) {
                console.warn(`⚠️ Skipped/Warning for ${p.name}: ${err.message}`);
            }
        }

        await browser.close();
        console.log(`\n🎉 Guru screenshots saved successfully to: ${SCREENSHOT_DIR}`);
        return;
    }

    // 0. Screenshot Landing Page
    console.log('📸 Capturing: 00_landing.png');
    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle2', timeout: 60000 });
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '00_landing.png'), fullPage: false });

    // 1. Screenshot Login Page (sebelum login)
    console.log('📸 Capturing: 01_login.png');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2', timeout: 60000 });
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '01_login.png'), fullPage: false });

    // 2. Perform Login as Admin
    console.log('🔑 Logging in as Admin (admin@siakadnuja.sch.id)...');
    await page.type('input[name="email"]', 'admin@siakadnuja.sch.id');
    await page.type('input[name="password"]', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }),
        page.click('button[type="submit"]')
    ]);
    console.log('✅ Logged in as Admin successfully!');

    // Daftar Halaman Admin
    const adminPagesToCapture = [
        { name: '02_dashboard.png', path: '/dashboard' },
        
        // Siswa
        { name: '03_siswa_index.png', path: '/siswa' },
        { name: '04_siswa_create.png', path: '/siswa/create' },
        { name: '05_siswa_detail.png', path: '/siswa/1' },
        { 
            name: '06_siswa_detail_modal_teguran.png', 
            path: '/siswa/1',
            action: async (p) => {
                try {
                    await p.evaluate(() => {
                        const btn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('Teguran'));
                        if (btn) btn.click();
                    });
                    await new Promise(r => setTimeout(r, 600));
                } catch (e) {
                    console.warn('Modal action failed:', e.message);
                }
            }
        },
        { name: '07_siswa_edit.png', path: '/siswa/1/edit' },

        // Guru
        { name: '08_guru_index.png', path: '/guru' },
        { name: '09_guru_create.png', path: '/guru/create' },
        { name: '10_guru_detail.png', path: '/guru/1' },
        { name: '11_guru_edit.png', path: '/guru/1/edit' },

        // Kelas
        { name: '12_kelas_index.png', path: '/kelas' },
        { name: '13_kelas_create.png', path: '/kelas/create' },
        { name: '14_kelas_detail.png', path: '/kelas/1' },
        { name: '15_kelas_edit.png', path: '/kelas/1/edit' },

        // Mata Pelajaran
        { name: '16_mapel_index.png', path: '/mata-pelajaran' },
        { name: '17_mapel_create.png', path: '/mata-pelajaran/create' },
        { name: '18_mapel_edit.png', path: '/mata-pelajaran/1/edit' },

        // Jadwal
        { name: '19_jadwal_index.png', path: '/jadwal' },
        { name: '20_jadwal_create.png', path: '/jadwal/create' },

        // Nilai & Absensi
        { name: '21_nilai_index.png', path: '/nilai' },
        { name: '22_absensi_index.png', path: '/absensi' },

        // Tagihan & Pembayaran
        { name: '23_tagihan_index.png', path: '/tagihan' },
        { name: '24_tagihan_create.png', path: '/tagihan/create' },
        { name: '25_tagihan_detail.png', path: '/tagihan/1' },
        { name: '26_tagihan_edit.png', path: '/tagihan/1/edit' },

        // Laporan
        { name: '27_laporan_index.png', path: '/laporan' },
        { name: '28_laporan_kehadiran.png', path: '/laporan/kehadiran?kelas_id=1&bulan=2026-08' },
        { name: '29_laporan_nilai.png', path: '/laporan/nilai?kelas_id=1&mapel_id=1' },

        // Pengumuman
        { name: '30_pengumuman_index.png', path: '/pengumuman' },
        { name: '31_pengumuman_create.png', path: '/pengumuman/create' },
        { name: '32_pengumuman_detail.png', path: '/pengumuman/1' },
        { name: '33_pengumuman_edit.png', path: '/pengumuman/1/edit' },

        // WhatsApp Gateway
        { name: '34_whatsapp_index.png', path: '/whatsapp' },
        { name: '35_whatsapp_templates.png', path: '/whatsapp/templates' },
        { name: '36_whatsapp_log_notifikasi.png', path: '/whatsapp/log-notifikasi' },
        { name: '37_whatsapp_log_chatbot.png', path: '/whatsapp/log-chatbot' },

        // Orang Tua & Users
        { name: '38_orang_tua_index.png', path: '/orang-tua' },
        { name: '39_users_index.png', path: '/users' },
        { name: '40_users_create.png', path: '/users/create' },
    ];

    for (const p of adminPagesToCapture) {
        try {
            const url = `${BASE_URL}${p.path}`;
            console.log(`📸 Capturing Admin: ${p.name} (${p.path})`);
            await page.goto(url, { waitUntil: 'networkidle2' });

            if (p.action) {
                await p.action(page);
            }

            const targetPath = path.join(SCREENSHOT_DIR, p.name);
            await page.screenshot({ path: targetPath, fullPage: false });
        } catch (err) {
            console.warn(`⚠️ Skipped/Warning for ${p.name}: ${err.message}`);
        }
    }

    // 3. Logout & Perform Login as Guru
    console.log(`🔑 Logging out Admin and logging in as Guru (${GURU_EMAIL})...`);
    const cookies = await page.cookies();
    await page.deleteCookie(...cookies);

    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2', timeout: 60000 });
    await page.type('input[name="email"]', GURU_EMAIL);
    await page.type('input[name="password"]', GURU_PASSWORD);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }),
        page.click('button[type="submit"]')
    ]);
    console.log('✅ Logged in as Guru successfully!');

    // Ambil screenshot halaman guru

    for (const p of guruPagesToCapture) {
        try {
            const url = `${BASE_URL}${p.path}`;
            console.log(`📸 Capturing Guru: ${p.name} (${p.path})`);
            await page.goto(url, { waitUntil: 'networkidle2' });

            if (p.action) {
                await p.action(page);
            }

            const targetPath = path.join(SCREENSHOT_DIR, p.name);
            await page.screenshot({ path: targetPath, fullPage: false });
        } catch (err) {
            console.warn(`⚠️ Skipped/Warning for ${p.name}: ${err.message}`);
        }
    }

    await browser.close();
    console.log(`\n🎉 All screenshots saved successfully to: ${SCREENSHOT_DIR}`);
}

takeScreenshots().catch(console.error);
