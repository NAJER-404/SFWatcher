<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $library->Title }} - Book Details</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen py-10 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-2xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Book Details</h1>
                <p class="text-sm text-slate-500">Overview for inventory record #{{ $library->id }}</p>
            </div>
            <a href="{{ route('librarys.index') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl shadow-sm transition">
                &larr; Back to Catalog
            </a>
        </div>

        <!-- Book Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-8 shadow-sm space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100/80 mb-2">
                        {{ $library->Genre }}
                    </span>
                    <h2 class="text-2xl font-extrabold text-slate-900 leading-snug">{{ $library->Title }}</h2>
                    <p class="text-base text-slate-500 font-medium mt-1">by <span class="text-slate-800 font-semibold">{{ $library->Author }}</span></p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Copies in Stock</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $library->Quantity }}</p>
                    <span class="inline-flex items-center gap-1 mt-1 text-xs font-medium {{ $library->Quantity > 10 ? 'text-emerald-600' : 'text-amber-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $library->Quantity > 10 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        {{ $library->Quantity > 0 ? 'Available for borrowing' : 'Out of stock' }}
                    </span>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Unit Price</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($library->Price, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Estimated book value</p>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between border-t border-slate-100">
                <p class="text-xs text-slate-400">Added on {{ $library->created_at ? $library->created_at->format('M d, Y') : 'N/A' }}</p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('librarys.edit', $library->id) }}"
                        class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold rounded-xl transition">
                        Edit Book
                    </a>
                    <form action="{{ route('librarys.destroy', $library->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-semibold rounded-xl transition"
                            onclick="return confirm('Are you sure you want to delete this book?')">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</body>

</html>
