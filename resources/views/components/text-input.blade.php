@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm']) }}>
