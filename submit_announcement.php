<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit an Announcement</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            bg: '#f6f5f0',
                            green: '#1b6b3e',
                            greenHover: '#155431',
                            border: '#e2dfd5',
                            label: '#5a5850',
                            text: '#222220',
                        }
                    },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f6f5f0;
            color: #222220;
            font-family: 'Inter', sans-serif;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #1b6b3e !important;
            box-shadow: 0 0 0 1px #1b6b3e;
        }

        textarea::-webkit-scrollbar {
            width: 6px;
        }
        textarea::-webkit-scrollbar-track {
            background: transparent;
        }
        textarea::-webkit-scrollbar-thumb {
            background: #d4d0c5;
            border-radius: 3px;
        }
    </style>
</head>
<body class="min-h-screen px-4 py-8 md:px-12 md:py-14 lg:px-20">

    <div class="max-w-6xl mx-auto">
        
        <div id="statusMessage" class="hidden mb-6 p-4 text-sm rounded-none border"></div>

        <header class="mb-8 md:mb-10">
            <p class="text-[11px] font-semibold tracking-wider text-brand-green uppercase mb-2">WRITE FOR NSBM NEWSDESK</p>
            <h1 class="font-serif text-4xl md:text-5xl lg:text-6xl text-gray-900 font-normal tracking-tight mb-3">Submit an announcement</h1>
            <p class="text-base text-gray-600 font-normal">An admin reviews every submission before it is published.</p>
        </header>

        <form id="announcementForm" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12" onsubmit="handleFormSubmit(event)">
            
            <div class="lg:col-span-8 flex flex-col gap-6">
                
                <div>
                    <label for="title" class="block text-[11px] font-bold tracking-wider text-brand-label uppercase mb-2">
                        TITLE
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required
                        placeholder="Announcement title" 
                        class="w-full bg-[#fcfcf9] border border-brand-border rounded-none px-4 py-3 text-base text-gray-800 placeholder-gray-400 transition-colors"
                    />
                </div>

                <div>
                    <label for="body" class="block text-[11px] font-bold tracking-wider text-brand-label uppercase mb-2">
                        DESCRIPTION
                    </label>
                    <textarea 
                        id="body" 
                        name="body" 
                        rows="14" 
                        required
                        placeholder="Write your announcement details here..." 
                        class="w-full bg-[#fcfcf9] border border-brand-border rounded-none px-4 py-3 text-base text-gray-800 placeholder-gray-400 transition-colors resize-y min-h-[320px]"
                    ></textarea>
                </div>

            </div>

            <div class="lg:col-span-4 flex flex-col gap-6">
                
                <div class="flex flex-col gap-3">
                    <button 
                        type="submit" 
                        class="w-full bg-brand-green hover:bg-brand-greenHover text-white font-bold py-3.5 px-4 text-sm transition-colors text-center"
                    >
                        Submit announcement
                    </button>
                    <button 
                        type="button" 
                        onclick="saveDraft()" 
                        class="w-full bg-[#fcfcf9] hover:bg-gray-100 text-gray-900 font-bold py-3 px-4 text-sm border border-brand-border transition-colors text-center"
                    >
                        Save draft
                    </button>
                </div>

                <div class="border border-brand-border bg-[#fbfbfa]">
                    <div class="p-5 border-b border-brand-border">
                        <h3 class="font-serif text-xl text-gray-900 font-normal">How it works</h3>
                    </div>
                    <div class="divide-y divide-brand-border">
                        <div class="p-4 flex items-start gap-4">
                            <span class="font-serif text-2xl text-brand-green leading-none pt-0.5">1</span>
                            <p class="text-xs text-gray-700 leading-relaxed pt-1">You submit the announcement.</p>
                        </div>
                        <div class="p-4 flex items-start gap-4">
                            <span class="font-serif text-2xl text-brand-green leading-none pt-0.5">2</span>
                            <p class="text-xs text-gray-700 leading-relaxed pt-1">An admin reads it.</p>
                        </div>
                        <div class="p-4 flex items-start gap-4">
                            <span class="font-serif text-2xl text-brand-green leading-none pt-0.5">3</span>
                            <p class="text-xs text-gray-700 leading-relaxed pt-1">It is published, or sent back with feedback.</p>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script>
        function handleFormSubmit(e) {
            e.preventDefault();
            const title = document.getElementById('title').value;
            const body = document.getElementById('body').value;

            if (!title.trim() || !body.trim()) {
                showMessage("Please fill in both title and description.", true);
                return;
            }

            showMessage("Announcement submitted successfully for admin review!");
            document.getElementById('announcementForm').reset();
        }

        function saveDraft() {
            const title = document.getElementById('title').value;
            if (!title.trim()) {
                showMessage("Please enter at least a title to save a draft.", true);
                return;
            }
            showMessage("Draft saved.");
        }

        function showMessage(msg, isError = false) {
            const status = document.getElementById('statusMessage');
            status.textContent = msg;
            status.classList.remove('hidden', 'bg-green-50', 'text-green-800', 'border-green-200', 'bg-red-50', 'text-red-800', 'border-red-200');
            
            if (isError) {
                status.classList.add('bg-red-50', 'text-red-800', 'border-red-200');
            } else {
                status.classList.add('bg-green-50', 'text-green-800', 'border-green-200');
            }
        }
    </script>
</body>
</html>
