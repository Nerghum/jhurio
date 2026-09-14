@extends('layouts.app')

@section('title', 'Jhurio - Handcrafted with Love')

@section('content')

<!-- Dynamic Sections (Homepage Builder) -->
<div class="space-y-12 pb-20 pt-4">



    @foreach($builderSections as $section)
        @if($section->type === 'hero')
            <x-hero-block :section="$section" :categories="$allCategories" />
        @elseif($section->type === 'flash_sale')
            <x-flash-sale-block :section="$section" />
        @elseif($section->type === 'image_text')
            <x-image-text-block :section="$section" />
        @elseif($section->type === 'faq')
            <x-faq-block :section="$section" />
        @else
            <x-product-block 
                :title="$section->title" 
                :subtitle="$section->subtitle" 
                :products="$section->products"
                :highlight="false"
            />
        @endif
    @endforeach
</div>
@endsection