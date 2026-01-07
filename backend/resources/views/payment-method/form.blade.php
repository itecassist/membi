<div class="space-y-6">

    <div>
        <x-input-label for="type" :value="__('Type')"/>
        <x-text-input id="type" name="type" type="text" class="mt-1 block w-full" :value="old('type', $paymentMethod?->type)" autocomplete="type" placeholder="Type"/>
        <x-input-error class="mt-2" :messages="$errors->get('type')"/>
    </div>
    <div>
        <x-input-label for="explanation" :value="__('Explanation')"/>
        <x-text-input id="explanation" name="explanation" type="text" class="mt-1 block w-full" :value="old('explanation', $paymentMethod?->explanation)" autocomplete="explanation" placeholder="Explanation"/>
        <x-input-error class="mt-2" :messages="$errors->get('explanation')"/>
    </div>
    <div>
        <x-input-label for="is_active" :value="__('Is Active')"/>
        <x-text-input id="is_active" name="is_active" type="text" class="mt-1 block w-full" :value="old('is_active', $paymentMethod?->is_active)" autocomplete="is_active" placeholder="Is Active"/>
        <x-input-error class="mt-2" :messages="$errors->get('is_active')"/>
    </div>
    <div>
        <x-input-label for="default" :value="__('Default')"/>
        <x-text-input id="default" name="default" type="text" class="mt-1 block w-full" :value="old('default', $paymentMethod?->default)" autocomplete="default" placeholder="Default"/>
        <x-input-error class="mt-2" :messages="$errors->get('default')"/>
    </div>
    <div>
        <x-input-label for="details" :value="__('Details')"/>
        <x-text-input id="details" name="details" type="text" class="mt-1 block w-full" :value="old('details', $paymentMethod?->details)" autocomplete="details" placeholder="Details"/>
        <x-input-error class="mt-2" :messages="$errors->get('details')"/>
    </div>
    <div>
        <x-input-label for="virtual_form_id" :value="__('Virtual Form Id')"/>
        <x-text-input id="virtual_form_id" name="virtual_form_id" type="text" class="mt-1 block w-full" :value="old('virtual_form_id', $paymentMethod?->virtual_form_id)" autocomplete="virtual_form_id" placeholder="Virtual Form Id"/>
        <x-input-error class="mt-2" :messages="$errors->get('virtual_form_id')"/>
    </div>

    <div class="flex items-center gap-4">
        <x-button>Submit</x-button>
    </div>
</div>
