{{--
    WhatsApp Floating Widget & Animated Chatbot Loop Demo
    Tailwind CSS + Alpine.js interactive simulator matching WhatsApp Web & SIAKAD NUJA dark aesthetic.
--}}
<div x-data="whatsappChatWidget()" x-init="initWidget()" class="relative z-50">

    {{-- Floating WhatsApp Action Button (Placed on the RIGHT side above back-to-top) --}}
    <div class="fixed bottom-20 right-6 z-50 flex items-center gap-3 flex-row-reverse">
        
        {{-- WhatsApp Main Trigger Button --}}
        <button @click="toggleOpen()"
                type="button"
                aria-label="Buka Chatbot WhatsApp"
                class="group relative flex h-13 w-13 sm:h-14 sm:w-14 items-center justify-center rounded-full bg-gradient-to-tr from-emerald-600 to-emerald-400 text-white shadow-xl shadow-emerald-600/30 hover:shadow-emerald-500/50 hover:scale-105 transition-all duration-300 active:scale-95 focus:outline-none">
            
            {{-- Pulse Ring --}}
            <span class="absolute -inset-1 rounded-full bg-emerald-500/30 animate-pulse group-hover:bg-emerald-500/50 transition"></span>

            {{-- Unread Badge --}}
            <span x-show="!open" class="absolute -top-1 -left-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-black text-white ring-2 ring-slate-950 shadow-md">
                1
            </span>

            {{-- WhatsApp Icon --}}
            <svg x-show="!open" class="h-6 w-6 sm:h-7 sm:w-7 fill-current transition-transform duration-300 group-hover:rotate-12" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.008 3.676 3.751-.983zm10.963-6.666c-.301-.15-1.785-.881-2.061-.982-.276-.101-.477-.15-.676.15-.199.301-.775.982-.95.1.18-.175.175-.351.075-.651-.15-.301-.75-1.428-1.05-2.025-.292-.581-.59-.5-.81-.511l-.69-.01c-.238 0-.627.09-.954.446-.328.357-1.254 1.227-1.254 2.993 0 1.766 1.284 3.473 1.463 3.713.18.239 2.527 3.86 6.123 5.415.855.37 1.523.592 2.044.757.859.273 1.64.234 2.257.142.689-.103 2.115-.865 2.416-1.702.301-.837.301-1.554.211-1.702-.09-.148-.291-.238-.592-.388z"/>
            </svg>

            {{-- Close Icon --}}
            <svg x-show="open" class="h-6 w-6 stroke-current transition-transform duration-300 rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Teaser / Notification Badge (Left of Button) --}}
        <div x-show="!open" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-x-4 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4"
             @click="toggleOpen()"
             class="hidden md:flex items-center gap-2 rounded-2xl bg-white/95 border border-emerald-500/30 px-3 py-1.5 text-xs font-medium text-slate-800 shadow-xl dark:bg-slate-900/95 dark:text-slate-200 dark:shadow-2xl backdrop-blur-xl cursor-pointer hover:border-emerald-400/50 hover:bg-white dark:hover:bg-slate-900 transition group">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 dark:bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600 dark:bg-emerald-500"></span>
            </span>
            <div class="flex flex-col text-right">
                <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center justify-end gap-1 text-[11px]">
                    Chatbot SIAKAD Yanuja
                    <span class="bg-emerald-500/15 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-[8px] px-1 py-0.2 rounded-full border border-emerald-500/30">Live</span>
                </span>
                <span class="text-[10px] text-slate-500 group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-slate-300 transition">Klik untuk simulasi balasan</span>
            </div>
        </div>
    </div>

    {{-- Simulated WhatsApp Popup Window (Right-aligned above trigger) --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-8 scale-90"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 scale-90"
         @click.away="open = false"
         class="fixed bottom-36 right-4 sm:right-6 w-full max-w-[360px] z-50 overflow-hidden rounded-3xl border border-emerald-500/30 bg-slate-950/95 text-slate-100 shadow-2xl shadow-emerald-950/50 backdrop-blur-2xl">
        
        {{-- Header Chat --}}
        <div class="relative bg-slate-900/90 p-3 border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="relative">
                    <div class="h-9 w-9 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-400 p-0.5 shadow-md">
                        <img src="{{ asset('images/logo.png') }}" alt="Bot Logo" class="h-full w-full rounded-full object-cover bg-slate-900">
                    </div>
                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-slate-900"></span>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white flex items-center gap-1 leading-tight">
                        SIAKAD NUJA Bot
                        <svg class="h-3.5 w-3.5 text-sky-400 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-1.9 14.7L6 12.6l1.4-1.4 2.7 2.7 6.9-6.9 1.4 1.4-8.3 8.3z"/></svg>
                    </h3>
                    <p class="text-[10px] text-emerald-400 font-medium flex items-center gap-1">
                        <span x-show="isTyping" class="animate-pulse">sedang mengetik...</span>
                        <span x-show="!isTyping">Online · Respon Otomatis</span>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-0.5">
                {{-- Restart Loop Button --}}
                <button @click="resetLoop()" 
                        title="Ulangi Simulasi Chat" 
                        type="button"
                        class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </button>
                
                {{-- Close Button --}}
                <button @click="open = false" 
                        type="button"
                        class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        {{-- WhatsApp Chat Content Area (Adjusted size & custom sleek scrollbar) --}}
        <div id="wa-chat-container" class="h-[260px] overflow-y-auto p-3 space-y-2.5 bg-[#0b141a]/95 bg-[radial-gradient(#1f2c34_1px,transparent_1px)] [background-size:14px_14px] scroll-smooth [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:bg-slate-700/60 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-transparent">
            
            {{-- System Timestamp Badge --}}
            <div class="flex justify-center my-1.5">
                <span class="bg-slate-800/80 text-slate-400 text-[9px] px-2.5 py-0.5 rounded-full border border-white/5 font-medium shadow-sm">
                    HARI INI · DEMO CHATBOT LIVE
                </span>
            </div>

            {{-- Message Bubbles List --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.from === 'user' ? 'flex justify-end' : 'flex justify-start'" class="w-full">
                    
                    {{-- Parent Message (Right / Green) --}}
                    <div x-show="msg.from === 'user'" 
                         x-transition:enter="transition ease-out duration-200 transform"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="max-w-[85%] rounded-xl rounded-tr-none bg-emerald-700 text-white p-2.5 shadow-md text-xs leading-relaxed border border-emerald-500/30">
                        <p class="font-normal whitespace-pre-line" x-text="msg.text"></p>
                        <div class="mt-1 flex items-center justify-end gap-1 text-[9px] text-emerald-200">
                            <span x-text="msg.time"></span>
                            {{-- Read Receipt Checkmarks --}}
                            <svg class="h-3 w-3 text-sky-300 fill-current" viewBox="0 0 24 24"><path d="M.41 13.41L6 19l1.41-1.41L1.83 12m4.58 4.59L18 5l1.41 1.41-12 12M22.59 6.41L11 18l-1.41-1.41L21.17 5"/></svg>
                        </div>
                    </div>

                    {{-- Bot Message (Left / Dark Slate) --}}
                    <div x-show="msg.from === 'bot'" 
                         x-transition:enter="transition ease-out duration-200 transform"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="max-w-[88%] rounded-xl rounded-tl-none bg-slate-800/90 text-slate-100 p-2.5 shadow-md text-xs leading-relaxed border border-white/10 backdrop-blur-sm">
                        <div class="text-[9px] font-bold text-emerald-400 mb-1 flex items-center justify-between border-b border-white/5 pb-1">
                            <span>Bot SIAKAD NUJA</span>
                            <span class="text-[8px] bg-slate-700/60 px-1 py-0.2 rounded text-slate-300">Otomatis</span>
                        </div>
                        <div class="whitespace-pre-line text-slate-200 font-sans text-[11px]" x-html="formatMarkdown(msg.text)"></div>
                        <div class="mt-1 text-right text-[9px] text-slate-400" x-text="msg.time"></div>
                    </div>
                </div>
            </template>

            {{-- Typing Indicator Bubble --}}
            <div x-show="isTyping" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="flex justify-start">
                <div class="rounded-xl rounded-tl-none bg-slate-800/90 border border-white/10 px-3 py-2 shadow-md flex items-center gap-1">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-bounce"></span>
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-bounce [animation-delay:0.2s]"></span>
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-bounce [animation-delay:0.4s]"></span>
                </div>
            </div>
        </div>

        {{-- Interactive Quick Option Chips --}}
        <div class="bg-slate-900/90 p-2 border-t border-white/10 flex items-center gap-1 overflow-x-auto no-scrollbar text-[11px]">
            <span class="text-[9px] uppercase font-bold text-slate-500 whitespace-nowrap px-0.5">Quick:</span>
            <button @click="sendUserMessage('MENU')" type="button" class="rounded-lg bg-slate-800 border border-slate-700 px-2 py-0.5 text-slate-300 hover:bg-emerald-600 hover:text-white transition whitespace-nowrap">
                MENU
            </button>
            <button @click="sendUserMessage('1')" type="button" class="rounded-lg bg-slate-800 border border-slate-700 px-2 py-0.5 text-slate-300 hover:bg-emerald-600 hover:text-white transition whitespace-nowrap">
                1. Info Nilai
            </button>
            <button @click="sendUserMessage('2')" type="button" class="rounded-lg bg-slate-800 border border-slate-700 px-2 py-0.5 text-slate-300 hover:bg-emerald-600 hover:text-white transition whitespace-nowrap">
                2. Absensi
            </button>
            <button @click="sendUserMessage('3')" type="button" class="rounded-lg bg-slate-800 border border-slate-700 px-2 py-0.5 text-slate-300 hover:bg-emerald-600 hover:text-white transition whitespace-nowrap">
                3. SPP
            </button>
        </div>

        {{-- Direct WhatsApp CTA Footer --}}
        <div class="bg-slate-950 p-2.5 border-t border-white/10 flex items-center justify-between gap-2">
            <div class="text-[10px] text-slate-400">
                Gateway WA YANUJA
            </div>
            <a href="https://wa.me/6287490429290?text=Halo%20SIAKAD%20NUJA" 
               target="_blank" 
               rel="noopener noreferrer"
               class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-[11px] px-2.5 py-1 shadow-md shadow-emerald-600/30 transition">
                <svg class="h-3 w-3 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654z"/></svg>
                Chat WhatsApp
            </a>
        </div>

    </div>

</div>

{{-- Alpine.js Script Component --}}
<script>
    function whatsappChatWidget() {
        return {
            open: false,
            isTyping: false,
            messages: [],
            loopTimer: null,
            stepIndex: 0,

            // Scenario Script Conversation Loop
            scenario: [
                { from: 'user', text: 'MENU', delayBefore: 800 },
                { 
                    from: 'bot', 
                    text: "🤖 *SIAKAD NURUL JADID*\n\nSelamat datang Bpk/Ibu Wali Murid.\nSistem Informasi Akademik & Layanan Notifikasi YANUJA.\n\nKetik nomor menu:\n1. 📊 Info Nilai & Rapor\n2. 📅 Rekap Kehadiran/Absensi\n3. 📢 Pengumuman Sekolah\n4. 💳 Status SPP & Keuangan",
                    delayBefore: 1200 
                },
                { from: 'user', text: '1', delayBefore: 2000 },
                { 
                    from: 'bot', 
                    text: "📊 *INFORMASI NILAI SISWA*\n\nNama: *Ahmad Fais*\nKelas: V-A MIS Nurul Jadid Karduluk\nNISN: 0081234591\n\n- Matematika: *88 (A)*\n- Bahasa Indonesia: *90 (A)*\n- IPA & Agroteknologi: *94 (A+)*\n- Al-Qur'an Hadits: *92 (A)*\n\nStatus: *Lulus Semester Ganjil ✅*\n\nKetik *MENU* untuk kembali.",
                    delayBefore: 1500 
                },
                { from: 'user', text: '2', delayBefore: 2200 },
                { 
                    from: 'bot', 
                    text: "📅 *REKAP ABSENSI SISWA*\n\nNama: *Ahmad Fais* (MIS V-A)\nBulan: Juli 2026\n\n- Hadir: *24 Hari*\n- Sakit: *1 Hari*\n- Izin: *0 Hari*\n- Alpa: *0 Hari*\n\nPersentase Kehadiran: *96% (Sangat Baik)* 🌟\n\nKetik *MENU* untuk kembali.",
                    delayBefore: 1500 
                }
            ],

            initWidget() {
                // Preload initial greeting
                this.messages = [
                    { 
                        from: 'bot', 
                        text: 'Halo Bpk/Ibu Wali Murid! 👋\nIni adalah simulasi otomatis Chatbot WhatsApp SIAKAD NUJA.', 
                        time: this.getCurrentTime() 
                    }
                ];
                
                // Auto start loop when widget opens
                this.$watch('open', (val) => {
                    if (val && this.messages.length <= 1) {
                        this.startLoop();
                    }
                });
            },

            toggleOpen() {
                this.open = !this.open;
                if (this.open && this.messages.length <= 1) {
                    this.startLoop();
                }
            },

            getCurrentTime() {
                const now = new Date();
                return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            formatMarkdown(text) {
                if (!text) return '';
                // Simple formatting: *bold* -> <strong>bold</strong>, newlines -> <br>
                let formatted = text
                    .replace(/\*(.*?)\*/g, '<strong>$1</strong>')
                    .replace(/\n/g, '<br>');
                return formatted;
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('wa-chat-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            },

            startLoop() {
                if (this.loopTimer) clearTimeout(this.loopTimer);
                this.stepIndex = 0;
                this.playNextStep();
            },

            resetLoop() {
                if (this.loopTimer) clearTimeout(this.loopTimer);
                this.messages = [
                    { 
                        from: 'bot', 
                        text: 'Halo Bpk/Ibu Wali Murid! 👋\nSimulasi dimuat ulang...', 
                        time: this.getCurrentTime() 
                    }
                ];
                this.stepIndex = 0;
                this.isTyping = false;
                setTimeout(() => this.startLoop(), 600);
            },

            playNextStep() {
                if (!this.open) return;

                if (this.stepIndex >= this.scenario.length) {
                    // Loop completed, wait 5 seconds then restart loop
                    this.loopTimer = setTimeout(() => {
                        this.resetLoop();
                    }, 5000);
                    return;
                }

                const item = this.scenario[this.stepIndex];

                this.loopTimer = setTimeout(() => {
                    if (item.from === 'bot') {
                        this.isTyping = true;
                        this.scrollToBottom();

                        // Simulate typing delay before bot responds
                        setTimeout(() => {
                            this.isTyping = false;
                            this.messages.push({
                                from: 'bot',
                                text: item.text,
                                time: this.getCurrentTime()
                            });
                            this.scrollToBottom();
                            this.stepIndex++;
                            this.playNextStep();
                        }, 1200);
                    } else {
                        // User message
                        this.messages.push({
                            from: 'user',
                            text: item.text,
                            time: this.getCurrentTime()
                        });
                        this.scrollToBottom();
                        this.stepIndex++;
                        this.playNextStep();
                    }
                }, item.delayBefore);
            },

            sendUserMessage(text) {
                // Allow user to manually send a quick option chip
                this.messages.push({
                    from: 'user',
                    text: text,
                    time: this.getCurrentTime()
                });
                this.scrollToBottom();

                // Trigger bot response based on quick input
                this.isTyping = true;
                setTimeout(() => {
                    this.isTyping = false;
                    let replyText = '';
                    if (text === 'MENU') {
                        replyText = "🤖 *SIAKAD NURUL JADID*\n\n1. 📊 Info Nilai & Rapor\n2. 📅 Rekap Kehadiran\n3. 📢 Pengumuman\n4. 💳 Status SPP";
                    } else if (text === '1') {
                        replyText = "📊 *INFORMASI NILAI SISWA*\nNama: *Ahmad Fais*\nKelas: V-A MIS Nurul Jadid\n\n- Matematika: *88 (A)*\n- B. Indonesia: *90 (A)*\n- IPA: *94 (A+)*\n\nStatus: *Lulus* ✅";
                    } else if (text === '2') {
                        replyText = "📅 *REKAP ABSENSI*\nNama: *Ahmad Fais*\n\n- Hadir: *24 Hari*\n- Sakit: *1 Hari*\n- Kehadiran: *96%* 🌟";
                    } else if (text === '3') {
                        replyText = "💳 *STATUS SPP*\nNama: *Ahmad Fais*\n\n- Juli 2026: *LUNAS ✅*\n- Agustus 2026: *LUNAS ✅*";
                    } else {
                        replyText = "Ketik *MENU* untuk menampilkan pilihan.";
                    }

                    this.messages.push({
                        from: 'bot',
                        text: replyText,
                        time: this.getCurrentTime()
                    });
                    this.scrollToBottom();
                }, 1000);
            }
        }
    }
</script>
