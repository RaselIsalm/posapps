<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-[0_8px_20px_rgb(79,70,229,0.3)] hover:shadow-[0_8px_25px_rgb(79,70,229,0.45)] hover:from-indigo-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 active:scale-95 transition-all duration-300']) }}>
    {{ $slot }}
</button>
