@extends('layouts.app')

@section('title', 'Jhurio - Handcrafted with Love')

@section('content')

<!-- Dynamic Sections (Homepage Builder) -->
<div class="space-y-6 sm:space-y-8 lg:space-y-10 pt-2 sm:pt-4">



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
                :section="$section"
            />
        @endif
    @endforeach
</div>
@endsection