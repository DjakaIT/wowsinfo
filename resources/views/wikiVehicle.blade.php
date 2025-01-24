@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@php
    // Function to calculate torpedoDPM
    function torpedoDPM($guns, $barrels, $dmg, $reload) {
        $dmgSum = ($guns * $barrels) * $dmg;
        return round(($dmgSum / $reload) * 60);
    }
@endphp

@section('content')
<div class="wiki">
    <div class="wiki-vehicle-content">
        @if(false) 
          <p>Loading</p>
        @else
            <div class="container">
                <!-- Page Title and Breadcrumb -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h1 class="page-heading">{{ $name }}</h1>
                        <ul class="wiki-breadcrumb">
                          <li><a href="/wiki/" class="router-link-active"> Wiki </a><span> / &nbsp;</span></li>
                          <li><a href="/wiki/usa" class="router-link-active">Usa</a><span> / &nbsp;</span></li>
                          <li><a href="/wiki/cruiser" class="router-link-active">Cruiser</a><span> / &nbsp;</span></li>
                          <li><span>{{ $name }}</span></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Description, Image, and Info -->
                <div class="row">
                    <div class="col-md-8 wiki-ship-image">
                        <img src="{{ $image }}" alt="{{ $name }}">
                    </div>
                    <div class="col-md-4">
                        <h2>Decription</h2>
                        <p>{{ $description }}</p>
                        <div class="border-separator"></div>
                        <ul class="wiki-info-list">
                            <li><span class="bullet">Nation: </span><span class="value">{{ $nation }}</span></li>
                            <li><span class="bullet">Tier: </span><span class="value">{{ $tier }}</span></li>
                            <li><span class="bullet">Type: </span><span class="value">{{ $type }}</span></li>
                            @if($price_credit > 0)
                                <li><span class="bullet">Price credit: </span><span class="value">{{ $price_credit }}</span></li>
                            @endif
                            @if($price_gold > 0)
                                <li><span class="bullet">Price gold: </span><span class="value">{{ $price_gold }}</span></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <!-- Modules Info -->
                <div class="row mt-4">
                    <div class="col-12">
                      <h2 class="page-heading">Basic configuration</h2>
                    </div>
                </div>
                <div class="row">
                    <!-- Modules Column -->
                    <div class="col-md-4">
                        <div class="wiki-module-title-holder">
                            <h3 class="wiki-module-title">Modules</h3>
                            {{-- <span class="wiki-switch pointer" onclick="switchModuleInfo()">{{ __($moduleSwitchText) }}</span> --}}
                        </div>
                        <ul class="modules-tree-list">
                            @foreach($modules['default'] as $key => $module)
                            <li>
                                <div class="module-box">
                                    <div class="module-title-box">
                                        <img src="{{ $module['image'] }}" class="module-image" alt="{{ $module['type'] }}">
                                    </div>
                                    <p class="module-title">{{ $module['type'] }}</p>
                                    <p>
                                      {{ $module['name'] }}
                                    </p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
            
                    <!-- Performance Column -->
                    <div class="col-md-4">
                        <div class="wiki-module-title-holder">
                          <h3 class="wiki-module-title">Performance</h3>
                        </div>
                        @foreach($performance as $key => $stat)
                          <div class="stat-box mb-3">
                              <p class="module-title">{{ $key }} - {{ $stat['total'] }}%</p>
                              <div class="stats-bar-holder">
                                  <div class="stat-bar" style="width: {{ $stat['total'] }}%"></div>
                              </div>
                          </div>
                        @endforeach
                    </div>
                     
                    <!-- Armament Column -->
                    <div class="col-md-4">
                        <div class="wiki-module-title-holder">
                            <h3 class="wiki-module-title">Armament</h3>
                        </div>
                        @foreach($armament as $key => $weapon)
                            <div class="stat-box mb-3">
                                <p class="module-title">{{ $key }} - {{ $weapon }}%</p>
                                <div class="stats-bar-holder">
                                    <div class="stat-bar" style="width: {{ $weapon }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <!-- Detailed Profile -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h2 class="page-heading">Details</h2>
                    </div>
                    <div class="col-12 mb-50">
                        <div class="row">
                          <!-- Hull -->
                            @if(!empty($details['hull']))
                                <div class="col-md-4">
                                    <div class="wiki-module-title-holder">
                                        <h3 class="wiki-module-title">Hull</h3>
                                    </div>
                                    @foreach($details['hull'] as $key => $stat)
                                      <p class="wikie-details-subtitles">{{ $key }}</p>
                                      <p>{{ $stat }}</p>
                                    @endforeach
                                </div>
                            @endif
                            <!-- Mobility -->
                            @if(!empty($details['mobility']))
                                <div class="col-md-4">
                                    <div class="wiki-module-title-holder">
                                        <h3 class="wiki-module-title">Mobility</h3>
                                    </div>
                                    @foreach($details['mobility'] as $key => $stat)
                                      <p class="wikie-details-subtitles">{{ $key }}</p>
                                      <p>{{ $stat }}</p>
                                    @endforeach
                                </div>
                            @endif
                            <!-- Concealment -->
                            @if(!empty($details['concealment']))
                                <div class="col-md-4">
                                    <div class="wiki-module-title-holder">
                                        <h3 class="wiki-module-title">Concealment</h3>
                                    </div>
                                    @foreach($details['concealment'] as $key => $stat)
                                      <p class="wikie-details-subtitles">{{ $key }}</p>
                                      <p>{{ $stat }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 mb-50">
                        <div class="row">
                            <!-- Artilery -->
                            @if(!empty($details['artilery']))
                              <div class="col-5">
                                  <div class="wiki-module-title-holder">
                                      <h3 class="wiki-module-title">Artilery</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col-6">
                                      <p class="wikie-details-subtitles">Main turrets:</p>
                                      @foreach($details['artilery']['slots'] as $key => $slot)
                                        <p>
                                          {{ $slot['guns'] }} x {{ $slot['name'] }}
                                        </p>
                                      @endforeach
                                      <p class="wikie-details-subtitles">Hull turrets:</p>
                                      <p>
                                        {{ $details['hull']['atba_barrels'] }}
                                      </p>
                                      <p class="wikie-details-subtitles">Firing Range:</p>
                                      <p>{{ $details['artilery']['distance'] }} km</p>
                                      <p class="wikie-details-subtitles">Rate of fire:</p>
                                      <p>{{ $details['artilery']['gun_rate'] }} rounds/min</p>
                                      <p class="wikie-details-subtitles">Maximum dispersion:</p>
                                      <p>{{ $details['artilery']['max_dispersion'] }} m</p>
                                      <p class="wikie-details-subtitles">180 Degree Turn Time:</p>
                                      <p>{{ $details['artilery']['rotation_time'] }} sec</p>
                                      <p class="wikie-details-subtitles">Reload time:</p>
                                      <p>{{ $details['artilery']['shot_delay'] }} sec</p>
                                    </div>
                                    <div class="col-6">
                                      @foreach($details['artilery']['shells'] as $key => $shell)
                                        <div>
                                          <p class="wikie-details-subtitles">{{ $key }} shell</p>
                                          <p>{{ $shell['name'] }}</p>
                                          <p>Damage: {{ $shell['damage'] }}</p>
                                          <p>Shell speed: {{ $shell['bullet_speed'] }}</p>
                                          @if(!empty($shell['burn_probability']))
                                            <p>
                                              Chance of burning: {{ $shell['burn_probability'] }}
                                            </p>
                                          @endif
                                          <p>Bomb weight: {{ $shell['bullet_mass'] }}</p>
                                        </div>
                                      @endforeach
                                    </div>
                                  </div>
                              </div>
                            @endif
                            <!-- Secondary armament -->
                            @if(!empty($details['atbas']) && $details['atbas']['distance'] > 0)
                              <div class="col">
                                  <div class="wiki-module-title-holder">
                                    <h3 class="wiki-module-title">Armament</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col">
                                      <p class="wikie-details-subtitles">Hull turrets:</p>
                                      <p>{{ $details['hull']['atba_barrels'] }}</p>
                                      <p class="wikie-details-subtitles">Range:</p>
                                      <p>{{ $details['atbas']['distance'] }} km</p>
                                      @foreach($details['atbas']['slots'] as $key => $slot)
                                        <div>
                                          <p class="wikie-details-subtitles">{{ $slot['name'] }} - {{ $slot['type'] }}:</p>
                                          <p>Damage: {{ $slot['damage'] }}</p>
                                          <p>Chance of burning: {{ $slot['burn_probability'] }}%</p>
                                          <p>Shell speed: {{ $slot['bullet_speed'] }} m/s</p>
                                          <p>Bomb weight: {{ $slot['bullet_mass'] }} kg</p>
                                          <p>Rate of fire: {{ $slot['gun_rate'] }} rounds/min</p>
                                          <p>Reload time: {{ $slot['shot_delay'] }} sec</p>
                                        </div>
                                      @endforeach
                                    </div>
                                  </div>
                              </div>
                            @endif
                            <!-- Torpedos -->
                            @if(!empty($details['torpedos']))
                              <div class="col">
                                  <div class="wiki-module-title-holder">
                                    <h3 class="wiki-module-title">Torpedos</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col">
                                      <p class="wikie-details-subtitles">{{ $details['torpedos']['torpedo_name'] }}:</p>
                                      <p>Damage: {{ $details['torpedos']['max_damage'] }}</p>
                                      <p>Firing Range: {{ $details['torpedos']['distance'] }} km</p>
                                      <p>Torpedo speed: {{ $details['torpedos']['torpedo_speed'] }} knots</p>
                                      <p>Reload time: {{$details['torpedos']['reload_time'] }} sec</p>
                                      <p>180 Degree Turn Time: {{ $details['torpedos']['rotation_time'] }} sec</p>
                                      @foreach($details['torpedos']['slots'] as $key => $slot)
                                        <div>
                                          <p class="wikie-details-subtitles">
                                            Tube - {{ $slot['name'] }}:
                                          </p>
                                          <p>Caliber: {{ $slot['caliber'] }} mm</p>
                                          <p>Guns: {{ $slot['guns'] }}</p>
                                          <p>Torpedo tubes: {{ $slot['barrels'] }}</p>
                                          <p>Damage per salve:
                                            {{ ($slot['guns'] * $slot['barrels']) * $details['torpedos']['max_damage'] }}
                                          </p>
                                          <p>Damage per min:
                                            {{ torpedoDPM(
                                                $slot['guns'],
                                                $slot['barrels'],
                                                $details['torpedos']['max_damage'],
                                                $details['torpedos']['reload_time']
                                              ) }}
                                          </p>
                                        </div>
                                      @endforeach
                                    </div>
                                  </div>
                              </div>
                            @endif
                            <!-- Anti Aircraft -->
                            @if(!empty($details['anti_aircraft']))
                              <div class="col">
                                  <div class="wiki-module-title-holder">
                                    <h3 class="wiki-module-title">Anti Aircraft</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col">
                                      @foreach($details['anti_aircraft']['slots'] as $key => $slot)
                                        <div>
                                          <p class="wikie-details-subtitles">
                                            {{ $slot['name'] }}:
                                          </p>
                                          <p>Celiber: {{ $slot['caliber'] }} mm</p>
                                          <p>Guns: {{ $slot['guns'] }}</p>
                                        </div>
                                      @endforeach
                                    </div>
                                  </div>
                              </div>
                            @endif
                            <!-- Sonar -->
                            @if(!empty($details['submarine_sonar']))
                              <div class="col">
                                  <div class="wiki-module-title-holder">
                                    <h3 class="wiki-module-title">Sonar</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col">
                                    <p class="wikie-details-subtitles">
                                      Duration of a ping effect on a sector highlighted once
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_duration_0'] }} s</p>
                                    <p class="wikie-details-subtitles">
                                      Duration of a ping effect on a sector highlighted twice
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_duration_1'] }} s</p>
                                    <p class="wikie-details-subtitles">
                                      Maximum range
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_max_dist'] }}</p>
                                    <p class="wikie-details-subtitles">
                                      Reload time
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_shot_delay'] }}</p>
                                    <p class="wikie-details-subtitles">
                                      Ping velocity
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_speed_max'] }}</p>
                                    <p class="wikie-details-subtitles">
                                      Ping width
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_width'] }}</p>
                                    </div>
                                  </div>
                              </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection