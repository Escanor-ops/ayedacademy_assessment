<script src="https://technobond.net/includes/layouts/js/font.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="{{asset('layouts/js/owl.carousel.js')}}"></script>
    <script src="{{asset('layouts/js/owl.navigation.js')}}"></script>
    <script src="{{asset('layouts/js/owl.autoplay.js')}}"></script>
    <script src="{{asset('layouts/js/scripts.js')}}"></script>


    <!-- Custom navbar -->
    <script>
        const _show_navbar_category = elem => {
            const category = elem.querySelector('.dropdown-menu')
            category.classList.add('show')
        }
        const _hide_navbar_category = elem => {
            const category = elem.querySelector('.dropdown-menu')
            category.classList.remove('show')
        }
    </script>

   
</body>
</html>