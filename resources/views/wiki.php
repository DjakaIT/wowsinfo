@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
	<div class="wiki">
		<h1 class="page-heading mb-50">Warships wiki</h1>
		<div class="mb-50">
			<p class="wiki-text-info">World of Warships is a naval action MMO, dipping into the world of large-scale sea battles of the first half of the twentieth century. Epic battles rage across the oceans of the world in order to claim victory among teams comprised of the greatest representatives from the era of multi-ton marine giants. In order to achieve victory in battle, players must employ a wide range of strategies in a variety of tactical decisions. Sudden ambushes, cunning flanking attacks, open confrontation and 'head-on' assaults — captains must strive to find an ideal way to deliver a decisive blow at the enemy. Tactical diversity in World of Warships comes from the inclusion of many different classes of warships, including: aircraft carriers, capable of providing remote air support and striking targets at extreme range; colossal battleships that project power across vast swaths of ocean; light and heavy cruisers with the capability to quickly respond to changing battlefield conditions; and stealthy, agile destroyers which can be highly effective in group attacks.</p>
		</div>
		<h2 class="page-subheading">Nations</h2>
		<div class="wiki-nation-group mb-50">
			@foreach ($wiki['nations'] as $nation)
				<div class="wiki-nation-item">
					<a href="{{ route('getWikiPage', ['nation' => $nation]) }}">
						<img :src="$nation" />
					</a>
				</div>
			@endforeach
		</div>
		<h2 class="page-subheading">Warship types</h2>
		<div class="wiki-type-group-home">
			@foreach ($wiki['types'] as $type)
				<div class="wiki-type-item">
					<a href="{{ route('getWikiPage', ['type' => $type]) }}">
						<img :src="$type" />
						<span>{{ $type }}</span>
					</a>
				</div>
			@endforeach
		</div>
	</div>
@endsection