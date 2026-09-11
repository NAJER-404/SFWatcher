<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Library Management System</title>
    <!-- Google Fonts & Tailwind CDN for 100% reliable instant rendering -->
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
    <div class="max-w-6xl mx-auto space-y-8">

        <!-- Top Navigation / Header -->
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-indigo-600 text-white rounded-xl shadow-md shadow-indigo-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Library Catalog</h1>
                    <p class="text-sm text-slate-500 font-medium">Inventory tracking & book management</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('librarys.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white text-sm font-semibold rounded-xl shadow-sm shadow-indigo-200 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Book
                </a>
            </div>
        </header>

        <!-- Flash Success Notification -->
        @if (session('success'))
            <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Library Statistics Cards -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900">Library Statistics</h2>
                <span class="text-xs font-semibold text-slate-500 bg-slate-200/70 px-2.5 py-1 rounded-full">Real-time DB Data</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Total Books -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Books</p>
                            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalBooks) }}</p>
                            <p class="text-xs text-slate-500 mt-1">Unique titles in database</p>
                        </div>
                        <div class="p-3.5 bg-indigo-50 text-indigo-600 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Quantity -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Quantity</p>
                            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalQuantity) }}</p>
                            <p class="text-xs text-slate-500 mt-1">Physical copies in inventory</p>
                        </div>
                        <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Inventory Worth -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Inventory Worth</p>
                            <p class="text-3xl font-extrabold text-slate-900 mt-1">${{ number_format($totalValue, 2) }}</p>
                            <p class="text-xs text-slate-500 mt-1">Sum of (Quantity &times; Price)</p>
                        </div>
                        <div class="p-3.5 bg-amber-50 text-amber-600 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <form action="{{ route('librarys.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title, author, or genre..."
                        class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                </div>

                @if(isset($genres) && count($genres) > 0)
                <select name="genre" class="px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white text-slate-700">
                    <option value="all">All Genres</option>
                    @foreach($genres as $genre)
                        <option value="{{ $genre }}" {{ request('genre') == $genre ? 'selected' : '' }}>{{ $genre }}</option>
                    @endforeach
                </select>
                @endif

                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl transition">
                    Filter
                </button>

                @if(request()->has('search') || (request()->has('genre') && request('genre') !== 'all'))
                    <a href="{{ route('librarys.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl text-center transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Books Table Section -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900">Book Records</h3>
                <span class="text-xs text-slate-500 font-medium">Showing {{ count($librarys) }} entries</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm text-left">
                    <thead class="bg-slate-50/75 text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 font-bold uppercase tracking-wider text-xs">Title</th>
                            <th scope="col" class="px-6 py-3.5 font-bold uppercase tracking-wider text-xs">Author</th>
                            <th scope="col" class="px-6 py-3.5 font-bold uppercase tracking-wider text-xs">Genre</th>
                            <th scope="col" class="px-6 py-3.5 font-bold uppercase tracking-wider text-xs text-center">In Stock</th>
                            <th scope="col" class="px-6 py-3.5 font-bold uppercase tracking-wider text-xs text-right">Price</th>
                            <th scope="col" class="px-6 py-3.5 font-bold uppercase tracking-wider text-xs text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($librarys as $library)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 font-semibold text-slate-900 max-w-xs truncate">
                                    {{ $library->Title }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                    {{ $library->Author }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100/80">
                                        {{ $library->Genre }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $library->Quantity > 10 ? 'bg-emerald-50 text-emerald-700' : ($library->Quantity > 0 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $library->Quantity > 10 ? 'bg-emerald-500' : ($library->Quantity > 0 ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                        {{ $library->Quantity }} copies
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap font-bold text-slate-900">
                                    ${{ number_format($library->Price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap text-xs font-semibold space-x-1.5">
                                    <a href="{{ route('librarys.show', $library->id) }}"
                                        class="inline-block px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
                                        View
                                    </a>
                                    <a href="{{ route('librarys.edit', $library->id) }}"
                                        class="inline-block px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('librarys.destroy', $library->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg transition"
                                            onclick="return confirm('Are you sure you want to delete \'{{ addslashes($library->Title) }}\'?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <p class="font-medium text-slate-600">No books found in the library.</p>
                                        <p class="text-xs text-slate-400">Run <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">php artisan db:seed</code> to seed sample books, or click "Add New Book".</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>

</html>
