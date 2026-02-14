 <script>
     function bootApplication(conf, bootingCallbacks, bootedCallbacks) {
         {{-- https://dev.to/hollowman6/solution-to-missing-domcontentloaded-event-when-enabling-both-html-auto-minify-and-rocket-loader-in-cloudflare-5ch8 --}}
         var inCloudFlare = true;

         window.addEventListener("DOMContentLoaded", function() {
             inCloudFlare = false;
         });

         if (document.readyState === "loading") {
             window.addEventListener("load", function() {
                 if (inCloudFlare) window.dispatchEvent(new Event("DOMContentLoaded"));
             });
         }

         window.addEventListener('DOMContentLoaded', function() {
             window.Innoclapps = CreateApplication(conf, bootingCallbacks, bootedCallbacks)
             Innoclapps.start();
         });
     }
 </script>
