@php
  use App\Helpers\FrontendHelper;
@endphp
@extends('layout.layout')

@section('metaTitle', __('seo_home_title'))
@section('metaDescription', __('seo_home_content'))
@section('metaKeywords', __('seo_home_keywords'))

@section('content')
<div class="container">
	<div class="row mb-20 mt-30">
		<div class="col">
			<iframe src="https://api.wn8.info/tools/wows/twitchlive.php" title="description" class="tw-frame" loading="lazy"></iframe>
		</div>
	</div>
	<div class="row mb-20">
		<div class="col">
			<h2 class="heading2">{{ __('home_table_title_1') }}</h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
									<th class="border-b">{{ __('nav_player') }}</th>
									<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topPlayersLast24Hours'] as $player)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($player['name']) && !empty($player['wid']))
											<a href="{{ route('player.page', [
												'locale' => app()->getLocale(),
												'server' => strtolower(session('server', 'eu')),
												'name' => $player['name'],
												'id' => $player['wid']
											]) }}">
												{{ $player['name'] }}
											</a>
										@else
												<span>Missing name or wid</span>
										@endif
									</td>
									<td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($player['wn8']) }}">{{ $player['wn8'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<p class="table-info-para">{{ __('home_table_player_text_1') }}</p>
		</div>
		<div class="col">
			<h2 class="heading2">{{ __('home_table_title_2') }}</h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
								<th class="border-b">{{ __('nav_player') }}</th>
								<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topPlayersLast7Days'] as $player)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($player['name']) && !empty($player['wid']))
											<a href="{{ route('player.page', [
												'locale' => app()->getLocale(),
												'server' => strtolower(session('server', 'eu')),
												'name' => $player['name'],
												'id' => $player['wid']
											]) }}">
												{{ $player['name'] }}
											</a>
										@else
												<span>Missing name or wid</span>
										@endif
									</td>
									<td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($player['wn8']) }}">{{ $player['wn8'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<p class="table-info-para"> {{ __('home_table_player_text_2') }}</p>
		</div>
		<div class="col">
			<h2 class="heading2">{{ __('home_table_title_3') }}</h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
								<th class="border-b">{{ __('nav_player') }}</th>
								<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topPlayersLastMonth'] as $player)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($player['name']) && !empty($player['wid']))
											<a href="{{ route('player.page', [
												'locale' => app()->getLocale(),
												'server' => strtolower(session('server', 'eu')),
												'name' => $player['name'],
												'id' => $player['wid']
											]) }}">
												{{ $player['name'] }}
											</a>
										@else
												<span>Missing name or wid</span>
										@endif
									</td>
									<td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($player['wn8']) }}">{{ $player['wn8'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<p class="table-info-para">{{ __('home_table_player_text_3') }}</p>
		</div>
	</div>
	<div class="row mb-20">
		<div class="col">
		<iframe src="https://api.wn8.info/tools/wows/ytvids.php" title="description" class="yt-frame" loading="lazy"></iframe>
		</div>
	</div>
	<div class="row mb-10">
		<div class="col">
			<h2 class="heading2">{{ __('home_table_title_4') }}</h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
								<th class="border-b">{{ __('nav_player') }}</th>
								<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topPlayersOverall'] as $player)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($player['name']) && !empty($player['wid']))
											<a href="{{ route('player.page', [
												'locale' => app()->getLocale(),
												'server' => strtolower(session('server', 'eu')),
												'name' => $player['name'],
												'id' => $player['wid']
											]) }}">
												{{ $player['name'] }}
											</a>
										@else
												<span>Missing name or wid</span>
										@endif
									</td>
									<td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($player['wn8']) }}">{{ $player['wn8'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<p class="table-info-para"> {{ __('home_table_player_text_4') }}</p>
		</div>
		<div class="col">
			<h2 class="heading2">{{ __('home_table_title_5') }}</h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
								<th class="border-b">{{ __('nav_clan') }}</th>
								<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topClans'] as $clan)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($clan['name']) && !empty($clan['wid']))
											<a href="{{ route('clan.page', [
												'locale' => app()->getLocale(),
												'server' => strtolower(session('server', 'eu')),
												'name' => $clan['tag'],
												'id' => $clan['wid']
											]) }}">
												{{ $clan['tag'] }}
											</a>
										@else
												<span>Missing name or wid</span>
										@endif
									</td>
									<td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($clan['wn8']) }}">{{ $clan['wn8'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<p class="table-info-para">{{ __('home_table_clan_text') }}</p>
		</div>
	</div>
</div>
@endsection
