{{--
    Colour picker for attribute values.

    Gives a native swatch plus a text field that stays a valid 6 digit hex
    value. Typing is normalised as you go ("#f0a" -> "#ff00aa"), and the
    hidden field is what actually gets submitted, so a half typed or invalid
    value can never be stored.

    Props: name, value, inputClass (optional)
--}}
@php
    $pickerId = 'cp' . substr(md5($name . ($value ?? '')), 0, 8);
    $initial = strtolower(trim((string) ($value ?? '')));

    if (preg_match('/^#?([0-9a-f]{6})$/i', $initial, $m)) {
        $initial = '#' . strtolower($m[1]);
    } elseif (preg_match('/^#?([0-9a-f]{3})$/i', $initial, $m)) {
        $r = $m[1][0] . $m[1][0];
        $g = $m[1][1] . $m[1][1];
        $b = $m[1][2] . $m[1][2];
        $initial = '#' . $r . $g . $b;
    } else {
        $initial = '';
    }
@endphp

<div class="flex items-center gap-2" x-data="{
        hex: @js($initial),
        get normalized() {
            let v = (this.hex || '').trim().toLowerCase().replace(/^#/, '');
            if (/^[0-9a-f]{3}$/.test(v)) {
                v = v[0]+v[0] + v[1]+v[1] + v[2]+v[2];
            }
            return /^[0-9a-f]{6}$/.test(v) ? '#' + v : '';
        },
        onInput() {
            const clean = (this.hex || '').trim().toLowerCase().replace(/^#/, '').replace(/[^0-9a-f]/g, '');
            this.hex = '#' + clean;
        },
     }">
    <input type="color" :value="normalized" @input="hex = $event.target.value" aria-label="Pick a color"
           class="h-9 w-11 shrink-0 cursor-pointer rounded-lg border border-slate-700/50 bg-slate-800/50 p-1">
    <input type="text" x-model="hex" @input="onInput()"
           placeholder="#000080" maxlength="7" spellcheck="false"
           class="w-28 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white text-sm font-mono focus:border-purple-500 focus:outline-none {{ $inputClass ?? '' }}">
    {{-- Only the normalised value is submitted, so an invalid hex is never stored. --}}
    <input type="hidden" name="{{ $name }}" :value="normalized">
</div>
