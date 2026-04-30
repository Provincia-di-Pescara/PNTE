@props(['state' => 'draft'])

@php
$config = match($state) {
    'submitted'          => ['label' => 'Inviata', 'tone' => 'info'],
    'waiting_clearances' => ['label' => 'Attesa nulla osta', 'tone' => 'amber'],
    'waiting_payment'    => ['label' => 'Attesa pagamento', 'tone' => 'amber'],
    'approved'           => ['label' => 'Autorizzata', 'tone' => 'success'],
    'rejected'           => ['label' => 'Respinta', 'tone' => 'danger'],
    default              => ['label' => 'Bozza', 'tone' => 'default'],
};
@endphp

<x-chip :tone="$config['tone']" :dot="true" {{ $attributes }}>
    {{ $config['label'] }}
</x-chip>
