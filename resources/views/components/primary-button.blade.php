<button {{ $attributes->merge(['type' => 'submit', 'class' => 'px-6 py-3 bg-[#c8ab7a] hover:bg-[#a38b62] transition duration-300 text-white font-medium text-sm rounded']) }}>
    {{ $slot }}
</button>
