@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<div class="home page-padding">
  <div class="container">
    <div class="row">
      <div class="col-12 image-page">
        <h1>Contact</h1><h2>Ingame</h2>Player: INUNO<h2>Mail form</h2>   
        <!-- Form --> 
          <!-- Name Field -->
          <div class="mb-3">
              <label for="name" class="form-label">Name:</label>
              <input 
                  type="text" 
                  id="name" 
                  name="name" 
                  class="form-control" 
                  placeholder="Enter name" 
                  required
              >
          </div>        
          <!-- Message Field -->
          <div class="mb-3">
              <label for="message" class="form-label">Message:</label>
              <textarea 
                  id="message" 
                  name="message" 
                  class="form-control" 
                  placeholder="Enter message..." 
                  rows="3"
                  required
              ></textarea>
          </div>
                  
          <!-- Buttons -->
          <button type="submit" class="btn btn-primary">Submit</button>
          <button type="reset" class="btn btn-secondary">Reset</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection