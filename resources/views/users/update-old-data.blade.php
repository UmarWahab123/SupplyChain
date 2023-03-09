@extends('sales.layouts.layout')

@section('title','Account Recievable | Supply Chain')
<style type="text/css">
  #example {
  padding-top: 5vh;
  min-height: 90vh;
  color: white;
}

a {
  color: grey;
}

body {
  background-color: #000 !important;
}
</style>
@section('content')
<div class="row mb-2">
@foreach($warehouses as $war)
<button class="btn button-st export-btn-recievable col-2 export_s_p_r mb-2" data-id="{{$war->id}}">Update {{$war->warehouse_title}} Reserved Quantity</button>
@endforeach
</div>
<div class="row">
@foreach($warehouses as $war)
<button class="btn button-st export-btn-recievable col-2 mb-2 update_bgk_stock mb-2" data-id="{{$war->id}}">Update {{$war->warehouse_title}} Stock Card</button>
@endforeach

</div>

 <div class="col-lg-12 col-md-12 mt-4">
  <div class="alert alert-primary export-alert-recievable d-none"  role="alert">
    <i class="  fa fa-spinner fa-spin"></i>
    <b> Data is Updating Please wait! Please wait.. </b>
  </div>

  <div class="alert alert-success export-alert-success-recievable d-none"  role="alert">
  <i class=" fa fa-check "></i>
    <b>Data Update Successfully !!!
    </b>
  </div>
</div>

<div class="row">
    <div class="col">
        <button class="pull_code">Restart Server</button>
    </div>
</div>

<div id="example" align="center">
  <br>
  <button id="summonFireworks" style="visibility: hidden;">Summon</button>
  <button id="destroyFireworks" style="visibility: hidden;">Destroy</button>

</div>

<div class="row">
    URL : {{config('app.ecom_url')}}
</div>
@endsection 
@section('javascript')
<script type="text/javascript">
	$(document).ready(function(){
	     $(document).on('click','.export_s_p_r',function(e){
      e.preventDefault();
      var id = $(this).data('id');
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
        $.ajax({
            method:"get",
            url:"{{route('update-old-record-for-cq-rq')}}",
            data:{st_id: id},          
            beforeSend:function(){
                    
                /*$('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
                $("#loader_modal").modal('show');*/
              $('.export_s_p_r').html('Please Wait...');
              $('.export_s_p_r').prop('disabled',true); 
           
            },
            success:function(data){            
                if(data.status==1)
                {
                  //swal("Wait!", "File is getting ready and will be available for download", "warning");

                    //$("#loader_modal").modal('hide');
                    $('.export-alert-success-recievable').addClass('d-none');
                    $('.export-alert-recievable').removeClass('d-none');
                    $('.export_s_p_r').html('EXPORT is being Prepared');
                    $('.export_s_p_r').prop('disabled',true); 
                    $('.download-btn-div ').addClass('d-none');

                    console.log("Calling Function from first part");
                    checkStatusForReceivingExport();
                }
                else if(data.status==2)
                {
                    $('.export-alert-success-recievable').addClass('d-none');
                    $('.export-alert-recievable').removeClass('d-none');
                    $('.export_s_p_r').html(data.msg);
                    $('.export_s_p_r').prop('disabled',true); 
                    $('.download-btn-div ').addClass('d-none');

                  checkStatusForReceivingExport();
                  //checkStatusForProducts();
                }
              
            },
            error: function(request, status, error){
                {{-- $("#loader_modal").modal('hide'); --}}
                $('.export-btn-recievable').html('Create New Export');
                $('.export-btn-recievable').prop('disabled',false); 

            }
      });
      
});

    $(document).on('click','.update_bgk_stock',function(e){
        var id = $(this).data('id');
      e.preventDefault();
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
        $.ajax({
            method:"get",
            url:"{{route('update-old-record-for-cq-rq')}}",
            data:{id: id},          
            beforeSend:function(){
                    
                /*$('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
                $("#loader_modal").modal('show');*/
              $('.update_bgk_stock').html('Please Wait...');
              $('.update_bgk_stock').prop('disabled',true); 
           
            },
            success:function(data){            
                if(data.status==1)
                {
                  //swal("Wait!", "File is getting ready and will be available for download", "warning");

                    //$("#loader_modal").modal('hide');
                    $('.export-alert-success-recievable').addClass('d-none');
                    $('.export-alert-recievable').removeClass('d-none');
                    $('.update_bgk_stock').html('EXPORT is being Prepared');
                    $('.update_bgk_stock').prop('disabled',true); 
                    $('.download-btn-div ').addClass('d-none');

                    console.log("Calling Function from first part");
                    checkStatusForReceivingExport();
                }
                else if(data.status==2)
                {
                    $('.export-alert-success-recievable').addClass('d-none');
                    $('.export-alert-recievable').removeClass('d-none');
                    $('.update_bgk_stock').html(data.msg);
                    $('.update_bgk_stock').prop('disabled',true); 
                    $('.download-btn-div ').addClass('d-none');

                  checkStatusForReceivingExport();
                  //checkStatusForProducts();
                }
              
            },
            error: function(request, status, error){
                {{-- $("#loader_modal").modal('hide'); --}}
                $('.export-btn-recievable').html('Create New Export');
                $('.export-btn-recievable').prop('disabled',false); 

            }
      });
      
});
	function checkStatusForReceivingExport()
  {
    $.ajax({
            method:"get",
            url:"{{route('recursive-old-data-status-for-cq-rq')}}",
            success:function(data){                           
                  if(data.status==1)
                  {
                    console.log("Status " +data.status);
                    setTimeout(
                      function(){
                        console.log("Calling Function Again");
                        checkStatusForReceivingExport();
                      }, 5000);
                  }    
                  else if(data.status==0)
                  {
                    console.log(data);
                    $('#summonFireworks').trigger('click');
                    var href="{{asset('storage/app')}}"+"/"+data.file_name;
                    var last_downloaded = data.last_downloaded;
                    $('.export-alert-success-recievable').removeClass('d-none');
                    $('.export-alert-recievable').addClass('d-none');
                    $('.export-btn-recievable').html('Create New Export');
                    $('.export-btn-recievable').prop('disabled',false); 
                    // $('.download-btn-div ').html('');
                    // $('.download-btn-div ').append('<a class="btn button-st download-btn" download style="flex: 0.4;" href="'+href+'">Download</a><span> <i> <b>&nbsp;&nbsp;&nbsp;Last Downloaded On : '+last_downloaded+'</b></i></span>');
                    // $('.download-btn-div ').removeClass('d-none');
                    $('.primary-btn').addClass('d-none');
                  }
                  else if(data.status==2)
                  {
                    $('.export-alert-success-recievable').addClass('d-none');
                    $('.export-alert-recievable').addClass('d-none');
                    $('.export-btn-recievable').html('Create New Export');
                    $('.export-btn-recievable').prop('disabled',false); 
                    $('.export-alert-another-user').addClass('d-none');
                    toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
                    console.log(data.exception);
                  }
              }
          });
  }
  $(document).on('click','.pull_code',function(){
    // alert('here');
    var form = new FormData();
    form.append("key", "1");
    form.append("server", "180.190.132.11");
    form.append("port", "234354");

    var settings = {
      "url": "http://192.168.100.239:4450",
      "method": "POST",
      "timeout": 0,
      "processData": false,
      "mimeType": "multipart/form-data",
      "contentType": "json",
      dataType: 'bytes',
      "data": form
    };

    $.ajax(settings).done(function (response) {
      console.log(response);
    });
  });
	 });

/* example functionality */
$( document ).ready(function() {
  $('#summonFireworks').click(function () {
    $("#example").fireworks();
    jQuery("#example").before(jQuery("canvas")); //this makes the canvas appear behind the example text
  });
  
  $('#destroyFireworks').click(function() {
    $("#example").fireworks('destroy');  
  }); 
});

/*
Adapted from http://jsfiddle.net/dtrooper/AceJJ/

TODO:
 * Try to get rid of ghosting
 * See if anything can be made more efficient
 * Make the canvas fit in the z-order
*/

(function( $ ) {
    var MAX_ROCKETS = 5,
        MAX_PARTICLES = 500;

    var FUNCTIONS = {
        'init': function(element) {
            var jqe = $(element);

            // Check this element isn't already inited
            if (jqe.data('fireworks_data') !== undefined) {
                console.log('Looks like this element is already inited!');
                return;
            }

            // Setup fireworks on this element
            var canvas = document.createElement('canvas'),
                canvas_buffer = document.createElement('canvas'),
                data = {
                    'element': element,
                    'canvas': canvas,
                    'context': canvas.getContext('2d'),
                    'canvas_buffer': canvas_buffer,
                    'context_buffer': canvas_buffer.getContext('2d'),
                    'particles': [],
                    'rockets': []
                };

            // Add & position the canvas
            if (jqe.css('position') === 'static') {
                element.style.position = 'relative';
            }
            element.appendChild(canvas);
            canvas.style.position = 'absolute';
            canvas.style.top = '0px';
            canvas.style.bottom = '0px';
            canvas.style.left = '0px';
            canvas.style.right = '0px';

            // Kickoff the loops
            data.interval = setInterval(loop.bind(this, data), 1000 / 50);

            // Save the data for later
            jqe.data('fireworks_data', data);
        },
        'destroy': function(element) {
            var jqe = $(element);

            // Check this element isn't already inited
            if (jqe.data('fireworks_data') === undefined) {
                console.log('Looks like this element is not yet inited!');
                return;
            }
            var data = jqe.data('fireworks_data');
            jqe.removeData('fireworks_data');

            // Stop the interval
            clearInterval(data.interval);

            // Remove the canvas
            data.canvas.remove();

            // Reset the elements positioning
            data.element.style.position = '';
        }
    };

    $.fn.fireworks = function(action) {
        // Assume no action means we want to init
        if (!action) {
            action = 'init';
        }

        // Process each element
        this.each(function() {
            FUNCTIONS[action](this);
        });

        // Chaining ftw :)
        return this;
    };

    function launch(data) {
        if (data.rockets.length < MAX_ROCKETS) {
            var rocket = new Rocket(data);
            data.rockets.push(rocket);
        }
    }

    function loop(data) {
        // Launch a new rocket
        launch(data);

        // Update screen size
        if (data.canvas_width != data.element.offsetWidth) {
            data.canvas_width = data.canvas.width = data.canvas_buffer.width = data.element.offsetWidth;
        }
        if (data.canvas_height != data.element.offsetHeight) {
            data.canvas_height = data.canvas.height = data.canvas_buffer.height = data.element.offsetHeight;
        }

        // Fade the background out slowly
        data.context_buffer.clearRect(0, 0, data.canvas.width, data.canvas.height);
        data.context_buffer.globalAlpha = 0.9;
        data.context_buffer.drawImage(data.canvas, 0, 0);
        data.context.clearRect(0, 0, data.canvas.width, data.canvas.height);
        data.context.drawImage(data.canvas_buffer, 0, 0);

        // Update the rockets
        var existingRockets = [];
        data.rockets.forEach(function(rocket) {
            // update and render
            rocket.update();
            rocket.render(data.context);

            // random chance of 1% if rockets is above the middle
            var randomChance = rocket.pos.y < (data.canvas.height * 2 / 3) ? (Math.random() * 100 <= 1) : false;

            /* Explosion rules
                 - 80% of screen
                - going down
                - close to the mouse
                - 1% chance of random explosion
            */
            if (rocket.pos.y < data.canvas.height / 5 || rocket.vel.y >= 0 || randomChance) {
                rocket.explode(data);
            } else {
                existingRockets.push(rocket);
            }
        });
        data.rockets = existingRockets;

        // Update the particles
        var existingParticles = [];
        data.particles.forEach(function(particle) {
            particle.update();

            // render and save particles that can be rendered
            if (particle.exists()) {
                particle.render(data.context);
                existingParticles.push(particle);
            }
        });
        data.particles = existingParticles;

        while (data.particles.length > MAX_PARTICLES) {
            data.particles.shift();
        }
    }

    function Particle(pos) {
        this.pos = {
            x: pos ? pos.x : 0,
            y: pos ? pos.y : 0
        };
        this.vel = {
            x: 0,
            y: 0
        };
        this.shrink = .97;
        this.size = 2;

        this.resistance = 1;
        this.gravity = 0;

        this.flick = false;

        this.alpha = 1;
        this.fade = 0;
        this.color = 0;
    }

    Particle.prototype.update = function() {
        // apply resistance
        this.vel.x *= this.resistance;
        this.vel.y *= this.resistance;

        // gravity down
        this.vel.y += this.gravity;

        // update position based on speed
        this.pos.x += this.vel.x;
        this.pos.y += this.vel.y;

        // shrink
        this.size *= this.shrink;

        // fade out
        this.alpha -= this.fade;
    };

    Particle.prototype.render = function(c) {
        if (!this.exists()) {
            return;
        }

        c.save();

        c.globalCompositeOperation = 'lighter';

        var x = this.pos.x,
            y = this.pos.y,
            r = this.size / 2;

        var gradient = c.createRadialGradient(x, y, 0.1, x, y, r);
        gradient.addColorStop(0.1, "rgba(255,255,255," + this.alpha + ")");
        gradient.addColorStop(0.8, "hsla(" + this.color + ", 100%, 50%, " + this.alpha + ")");
        gradient.addColorStop(1, "hsla(" + this.color + ", 100%, 50%, 0.1)");

        c.fillStyle = gradient;

        c.beginPath();
        c.arc(this.pos.x, this.pos.y, this.flick ? Math.random() * this.size : this.size, 0, Math.PI * 2, true);
        c.closePath();
        c.fill();

        c.restore();
    };

    Particle.prototype.exists = function() {
        return this.alpha >= 0.1 && this.size >= 1;
    };

    function Rocket(data) {
        Particle.apply(
            this,
            [{
                x: Math.random() * data.canvas.width * 2 / 3 + data.canvas.width / 6,
                y: data.canvas.height
            }]
        );

        this.explosionColor = Math.floor(Math.random() * 360 / 10) * 10;
        this.vel.y = Math.random() * -3 - 4;
        this.vel.x = Math.random() * 6 - 3;
        this.size = 2;
        this.shrink = 0.999;
        this.gravity = 0.01;
    }

    Rocket.prototype = new Particle();
    Rocket.prototype.constructor = Rocket;

    Rocket.prototype.explode = function(data) {
        var count = Math.random() * 10 + 80;

        for (var i = 0; i < count; i++) {
            var particle = new Particle(this.pos);
            var angle = Math.random() * Math.PI * 2;

            // emulate 3D effect by using cosine and put more particles in the middle
            var speed = Math.cos(Math.random() * Math.PI / 2) * 15;

            particle.vel.x = Math.cos(angle) * speed;
            particle.vel.y = Math.sin(angle) * speed;

            particle.size = 10;

            particle.gravity = 0.2;
            particle.resistance = 0.92;
            particle.shrink = Math.random() * 0.05 + 0.93;

            particle.flick = true;
            particle.color = this.explosionColor;

            data.particles.push(particle);
        }
    };

    Rocket.prototype.render = function(c) {
        if (!this.exists()) {
            return;
        }

        c.save();

        c.globalCompositeOperation = 'lighter';

        var x = this.pos.x,
            y = this.pos.y,
            r = this.size / 2;

        var gradient = c.createRadialGradient(x, y, 0.1, x, y, r);
        gradient.addColorStop(0.1, "rgba(255, 255, 255 ," + this.alpha + ")");
        gradient.addColorStop(0.2, "rgba(255, 180, 0, " + this.alpha + ")");

        c.fillStyle = gradient;

        c.beginPath();
        c.arc(this.pos.x, this.pos.y, this.flick ? Math.random() * this.size / 2 + this.size / 2 : this.size, 0, Math.PI * 2, true);
        c.closePath();
        c.fill();

        c.restore();
    };
}(jQuery));
</script>
@stop