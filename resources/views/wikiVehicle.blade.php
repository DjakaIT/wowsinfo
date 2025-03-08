
@extends('layout.layout')

@section('metaTitle', __('seo_wiki_vehicle_title'))
@section('metaDescription', __('seo_wiki_vehicle_content'))
@section('metaKeywords', __('seo_wiki_vehicle_keywords'))

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
                          <li><a href="{{ route('wiki.nation', ['nation' => $nation]) }}" class="router-link-active">{{ $nation }}</a><span> / &nbsp;</span></li>
                          <li><a href="{{ route('wiki.type', ['type' => $type]) }}" class="router-link-active">{{ $type }}</a><span> / &nbsp;</span></li>
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
                        <h2>{{ __('description') }}</h2>
                        <p>{{ $description }}</p>
                        <div class="border-separator"></div>
                        <ul class="wiki-info-list">
                          <li><span class="bullet">{{ __('nation') }}: </span><span class="value">{{ __('wiki_nation_' . $nation) }}</span></li>                            <li><span class="bullet">{{ __('tier') }}: </span><span class="value">{{ $tier }}</span></li>
                            <li><span class="bullet">{{ __('type') }}: </span><span class="value">{{ __('wiki_' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type)))) }}</span></li>
                            @if($price_credit > 0)
                                <li><span class="bullet">{{ __('wiki_price') }}: </span><span class="value">{{ $price_credit }}</span></li>
                            @endif
                            @if($price_gold > 0)
                                <li><span class="bullet">{{ __('wiki_price_gold') }} </span><span class="value">{{ $price_gold }}</span></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <!-- Modules Info -->
                <div class="row mt-4">
                    <div class="col-12">
                      <h2 class="page-heading">{{ __('_wiki_basic_configuration') }}</h2>
                    </div>
                </div>
                <div class="row">
                    <!-- Modules Column -->
                    <div class="col-md-4">
                        <div class="wiki-module-title-holder">
                            <h3 class="wiki-module-title">{{ __('wiki_modules') }}</h3>
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
                          <h3 class="wiki-module-title">{{ __('wiki_performance') }}</h3>
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
                            <h3 class="wiki-module-title">{{ __('_wiki_armament') }}</h3>
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
                        <h2 class="page-heading">{{ __('wiki_details') }}</h2>
                    </div>
                    <div class="col-12 mb-50">
                        <div class="row">
                          <!-- Hull -->
                            @if(!empty($details['hull']))
                                <div class="col-md-4">
                                    <div class="wiki-module-title-holder">
                                        <h3 class="wiki-module-title">{{ __('wiki_hull') }}</h3>
                                    </div>
                                    @foreach($details['hull'] as $key => $stat)
                                      @if($stat !== null)
                                        <p class="wikie-details-subtitles">{{ $key }}</p>
                                        <p>{{ $stat }}</p>
                                      @endif
                                    @endforeach
                                </div>
                            @endif
                            <!-- Mobility -->
                            @if(!empty($details['mobility']))
                                <div class="col-md-4">
                                    <div class="wiki-module-title-holder">
                                        <h3 class="wiki-module-title">{{ __('wiki_stat_mobility') }}</h3>
                                    </div>
                                    @foreach($details['mobility'] as $key => $stat)
                                      @if($stat !== null)
                                        <p class="wikie-details-subtitles">{{ $key }}</p>
                                        <p>{{ $stat }}</p>
                                      @endif
                                    @endforeach
                                </div>
                            @endif
                            <!-- Concealment -->
                            @if(!empty($details['concealment']))
                                <div class="col-md-4">
                                    <div class="wiki-module-title-holder">
                                        <h3 class="wiki-module-title">{{ __('wiki_stat_concealment') }}</h3>
                                    </div>
                                    @foreach($details['concealment'] as $key => $stat)
                                      @if($stat !== null)
                                        <p class="wikie-details-subtitles">{{ $key }}</p>
                                        <p>{{ $stat }}</p>
                                      @endif
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
                                      <h3 class="wiki-module-title">{{ __('wiki_artillery') }}</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col-6">
                                      <p class="wikie-details-subtitles">{{ __('_main_turrets') }}:</p>
                                      @foreach($details['artilery']['slots'] as $key => $slot)
                                        <p>
                                          {{ $slot['guns'] }} x {{ $slot['name'] }}
                                        </p>
                                      @endforeach
                                      <p class="wikie-details-subtitles">{{ __('wiki_hull') }}s:</p>
                                      <p>
                                        {{ $details['hull']['atba_barrels'] }}
                                      </p>
                                      <p class="wikie-details-subtitles">{{ __('_firing_range') }}:</p>
                                      <p>{{ $details['artilery']['distance'] }} {{ __('_m_km') }}</p>
                                      <p class="wikie-details-subtitles">{{ __('_rate_of_fire') }}:</p>
                                      <p>{{ $details['artilery']['gun_rate'] }} {{ __('_m_rounds_min') }}</p>
                                      <p class="wikie-details-subtitles">{{ __('_max_dispersion') }}:</p>
                                      <p>{{ $details['artilery']['max_dispersion'] }} {{ __('_m_m') }}</p>
                                      <p class="wikie-details-subtitles">{{ __('_rotation_time') }}:</p>
                                      <p>{{ $details['artilery']['rotation_time'] }} sec</p>
                                      <p class="wikie-details-subtitles">{{ __('_shot_delay') }}:</p>
                                      <p>{{ $details['artilery']['shot_delay'] }} sec</p>
                                    </div>
                                    <div class="col-6">
                                      @foreach($details['artilery']['shells'] as $key => $shell)
                                        <div>
                                          <p class="wikie-details-subtitles">{{ $key }} shell</p>
                                          <p>{{ $shell['name'] }}</p>
                                          <p>{{ __('_wiki_damage') }}: {{ $shell['damage'] }}</p>
                                          <p>{{ __('_bullet_speed') }}: {{ $shell['bullet_speed'] }}</p>
                                          @if(!empty($shell['burn_probability']))
                                            <p>
                                              {{ __('_burn_probability') }}: {{ $shell['burn_probability'] }}
                                            </p>
                                          @endif
                                          <p>{{ __('_bullet_mass') }}: {{ $shell['bullet_mass'] }}</p>
                                        </div>
                                      @endforeach
                                    </div>
                                  </div>
                              </div>
                            @endif
                            <!-- Secondary armament -->
                            @if(isset($details['atbas']['slots']) && $details['atbas']['distance'] > 0)
                              <div class="col">
                                  <div class="wiki-module-title-holder">
                                    <h3 class="wiki-module-title">{{ __('_wiki_armament') }}</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col">
                                      <p class="wikie-details-subtitles">{{ __('wiki_hull') }}:</p>
                                      <p>{{ $details['hull']['atba_barrels'] }}</p>
                                      <p class="wikie-details-subtitles">{{ __('_firing_range') }}:</p>
                                      <p>{{ $details['atbas']['distance'] }} km</p>
                                      @foreach($details['atbas']['slots'] as $key => $slot)
                                        <div>
                                          <p class="wikie-details-subtitles">{{ $slot['name'] }} - {{ $slot['type'] }}:</p>
                                          <p>{{ __('_wiki_damage') }}: {{ $slot['damage'] }}</p>
                                          <p>{{ __('_burn_probability') }}: {{ $slot['burn_probability'] }}%</p>
                                          <p>{{ __('_bullet_speed') }}: {{ $slot['bullet_speed'] }} m/s</p>
                                          <p>{{ __('_bullet_mass') }}: {{ $slot['bullet_mass'] }} kg</p>
                                          <p>{{ __('_rate_of_fire') }}: {{ $slot['gun_rate'] }} rounds/min</p>
                                          <p>{{ __('shot_delay') }}: {{ $slot['shot_delay'] }} sec</p>
                                        </div>
                                      @endforeach
                                    </div>
                                  </div>
                              </div>
                            @endif
                            <!-- Torpedos -->
                            @if(!is_null($details['torpedos']))
                              <div class="col">
                                  <div class="wiki-module-title-holder">
                                    <h3 class="wiki-module-title">Torpedos</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col">
                                      <p class="wikie-details-subtitles">{{ $details['torpedos']['torpedo_name'] }}:</p>
                                      <p>{{ __('_wiki_damage') }}: {{ $details['torpedos']['max_damage'] }}</p>
                                      <p>{{ __('_firing_range') }}: {{ $details['torpedos']['distance'] }} km</p>
                                      <p>{{ __('_torpedo_speed') }}: {{ $details['torpedos']['torpedo_speed'] }} knots</p>
                                      <p>{{ __('shot_delay') }}: {{$details['torpedos']['reload_time'] }} sec</p>
                                      <p>{{ __('_rotation_time') }}: {{ $details['torpedos']['rotation_time'] }} sec</p>
                                      @foreach($details['torpedos']['slots'] as $key => $slot)
                                        <div>
                                          <p class="wikie-details-subtitles">
                                            {{ __('_wiki_tube') }} - {{ $slot['name'] }}:
                                          </p>
                                          <p>{{ __('_wiki_caliber') }}: {{ $slot['caliber'] }} mm</p>
                                          <p>{{ __('_wiki_guns') }}: {{ $slot['guns'] }}</p>
                                          <p>{{ __('_torpedoes_barrels') }}: {{ $slot['barrels'] }}</p>
                                          <p>{{ __('_damage_per_salve') }}:
                                            {{ ($slot['guns'] * $slot['barrels']) * $details['torpedos']['max_damage'] }}
                                          </p>
                                          <p>{{ __('_damage_per_min') }}:
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
                            @if(!is_null($details['anti_aircraft']))
                              <div class="col">
                                  <div class="wiki-module-title-holder">
                                    <h3 class="wiki-module-title">{{ __('wiki_anti_aircraft') }}</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col">
                                      @foreach($details['anti_aircraft']['slots'] as $key => $slot)
                                        <div>
                                          <p class="wikie-details-subtitles">
                                            {{ $slot['name'] }}:
                                          </p>
                                          <p>{{ __('_wiki_caliber') }}: {{ $slot['caliber'] }} mm</p>
                                          <p>{{ __('_wiki_guns') }}: {{ $slot['guns'] }}</p>
                                        </div>
                                      @endforeach
                                    </div>
                                  </div>
                              </div>
                            @endif
                            <!-- Sonar -->
                            @if(!is_null($details['submarine_sonar']))
                              <div class="col">
                                  <div class="wiki-module-title-holder">
                                    <h3 class="wiki-module-title">{{ __('wiki_sonar') }}</h3>
                                  </div>
                                  <div class="row">
                                    <div class="col">
                                    <p class="wikie-details-subtitles">
                                      {{ __('_wave_duration_0') }}
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_duration_0'] }} s</p>
                                    <p class="wikie-details-subtitles">
                                      {{ __('_wave_duration_1') }}
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_duration_1'] }} s</p>
                                    <p class="wikie-details-subtitles">
                                      {{ __('_wiki_range') }}
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_max_dist'] }}</p>
                                    <p class="wikie-details-subtitles">
                                      {{ __('_wave_max_dist') }}
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_shot_delay'] }}</p>
                                    <p class="wikie-details-subtitles">
                                      {{ __('_wave_speed_max') }}
                                    </p>
                                    <p>{{ $details['submarine_sonar']['wave_speed_max'] }}</p>
                                    <p class="wikie-details-subtitles">
                                      {{ __('_wave_width') }}
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