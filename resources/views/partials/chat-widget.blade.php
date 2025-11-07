{{-- Widget de chat en ligne --}}
@if(setting('chat_widget_enabled', false))
    @if(setting('chat_provider') === 'tawk')
        <!-- Tawk.to -->
        <script type="text/javascript">
            var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
            (function(){
                var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
                s1.async=true;
                s1.src='https://embed.tawk.to/{{ setting("chat_tawk_id") }}/default';
                s1.charset='UTF-8';
                s1.setAttribute('crossorigin','*');
                s0.parentNode.insertBefore(s1,s0);
            })();
        </script>
    @elseif(setting('chat_provider') === 'crisp')
        <!-- Crisp -->
        <script type="text/javascript">
            window.$crisp=[];
            window.CRISP_WEBSITE_ID="{{ setting('chat_crisp_id') }}";
            (function(){
                d=document;
                s=d.createElement("script");
                s.src="https://client.crisp.chat/l.js";
                s.async=1;
                d.getElementsByTagName("head")[0].appendChild(s);
            })();
        </script>
    @endif
@endif

