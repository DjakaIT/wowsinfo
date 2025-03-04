@extends('layout.layout')

@section('metaTitle', __('_page_contact_title'))
@section('metaDescription', __('_page_contact_desc'))
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<div class="home page-padding">
  <div class="container">
    <div class="row">
      <div class="col-12 image-page">
        {!! __('_page_contact') !!}   
        <!-- Form --> 
        <form>
          <!-- Name Field -->
          <div class="mb-3">
              <label for="name" class="form-label">{{ __('_page_contact_name_button') }}</label>
              <input 
                  type="text" 
                  id="name" 
                  name="name" 
                  class="form-control" 
                  placeholder="{{ __('_page_contact_name_field') }}"
                  required
              >
          </div>        
          <!-- Message Field -->
          <div class="mb-3">
              <label for="message" class="form-label">{{ __('_page_contact_message_button') }}</label>
              <textarea 
                  id="message" 
                  name="message" 
                  class="form-control" 
                  placeholder="{{ __('_page_contact_message_field') }}"
                  rows="3"
                  required
              ></textarea>
          </div>
                  
          <!-- Buttons -->
          <button type="submit" class="btn btn-primary">{{ __('_page_contact_submit_button') }}</button>
          <button type="reset" class="btn btn-secondary">{{ __('_page_contact_reset_button') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection