@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 dark:focus:ring-indigo-500/50 dark:focus:border-indigo-400 block w-full p-3 transition-all duration-300 shadow-sm hover:border-slate-300 dark:hover:border-slate-600']) }}>
