@php
  use App\Helpers\FrontendHelper;
@endphp
@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<div class="container">
	<div class="row mb-20 mt-30">
		<div class="col">
			<iframe src="https://api.wn8.info/tools/wows/twitchlive.php" title="description" class="tw-frame"></iframe>
		</div>
	</div>
	<div class="row mb-20">
		<div class="col">
			<h2 class="heading2"> Top Player: Past 24 hours </h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
									<th class="border-b">Player</th>
									<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topPlayersLast24Hours'] as $player)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($player['name']) && !empty($player['wid']))
											<a href="{{ route('player.page', ['name' => $player['name'], 'id' => $player['wid']]) }}">
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
			<p class="table-info-para"> Statistics for players whose tier is above 5 and who have 5+ battles </p>
		</div>
		<div class="col">
			<h2 class="heading2"> Top Player: Past 7 days </h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
								<th class="border-b">Player</th>
								<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topPlayersLast7Days'] as $player)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($player['name']) && !empty($player['wid']))
											<a href="{{ route('player.page', ['name' => $player['name'], 'id' => $player['wid']]) }}">
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
			<p class="table-info-para"> Statistics for players whose tier is above 5 and who have 30+ battles </p>
		</div>
		<div class="col">
			<h2 class="heading2"> Top Player: Past month </h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
								<th class="border-b">Player</th>
								<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topPlayersLastMonth'] as $player)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($player['name']) && !empty($player['wid']))
											<a href="{{ route('player.page', ['name' => $player['name'], 'id' => $player['wid']]) }}">
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
			<p class="table-info-para"> Statistics for players whose tier is above 5 and who have 120+ battles </p>
		</div>
	</div>
	<div class="row mb-20">
		<div class="col">
		<iframe src="https://api.wn8.info/tools/wows/ytvids.php" title="description" class="yt-frame"></iframe>
		</div>
	</div>
	<div class="row mb-10">
		<div class="col">
			<h2 class="heading2"> Top Player: Overall </h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
								<th class="border-b">Player</th>
								<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topPlayersOverall'] as $player)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($player['name']) && !empty($player['wid']))
											<a href="{{ route('player.page', ['name' => $player['name'], 'id' => $player['wid']]) }}">
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
			<p class="table-info-para"> Statistics for players whose tier is above 5 and who have 500+ battles </p>
		</div>
		<div class="col">
			<h2 class="heading2"> Top Clans </h2>
			<div class="table-container">
				<div class="shadow4 customRedefine vehicleTable table-responsive mb-10">
					<table class="table b-table table-striped table-bordered">
						<thead>
							<tr class="bg-gray-100 text-left">
								<th class="border-b">Clan</th>
								<th class="border-b">WN8</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($statistics['topClans'] as $clan)
								<tr class="border-b">
									<td class="py-2 px-4">
										@if (!empty($clan['name']) && !empty($clan['wid']))
											<a href="{{ route('clan.page', ['name' => $clan['name'], 'id' => $clan['wid']]) }}">
												{{ $clan['name'] }}
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
			<p class="table-info-para"> Statistics for clans whose tier is above 5 and who have 120+ battles </p>
		</div>
	</div>
</div>
@endsection
