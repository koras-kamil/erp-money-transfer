<tr x-show="filteredRows.length === 0" x-transition.opacity class="bg-white">
    {{-- Dynamic Colspan to fit any table --}}
    <td :colspan="Object.keys(columns).length + 2" class="py-4 text-center border-b border-slate-50">
        <div class="flex justify-center items-center">
            <dotlottie-player 
                src="https://lottie.host/ace77418-be70-4ea4-8c0a-88efe0221c91/aCjbIohU9b.lottie" 
                background="transparent" 
                speed="1" 
                style="width: 150px; height: 150px;" 
                loop 
                autoplay>
            </dotlottie-player>
        </div>
    </td>
</tr>