@props([
    /** @var \App\Data\Charts\RadarChartData|array $data */
    'data',
    'height' => 260,
    'id' => 'radar-' . uniqid(),
])

@php
    $payload = $data instanceof \Spatie\LaravelData\Data ? $data->toArray() : (array) $data;
@endphp

<div class="w-full">
    <div class="relative" style="height: {{ (int) $height }}px">
        <canvas id="{{ $id }}" data-chart="radar" data-config='@json($payload)'
            aria-label="Radar de secciones" role="img" class="block w-full h-full evaluation-chart"></canvas>
    </div>
</div>
