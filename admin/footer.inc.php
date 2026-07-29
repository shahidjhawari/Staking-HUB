        </div><!-- /.main-panel -->
    </div><!-- /.admin-shell -->

    <!--   Core JS Files   -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Bootstrap Notify -->
    <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="assets/js/kaiadmin.min.js"></script>

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="assets/js/setting-demo.js"></script>
    <script src="assets/js/demo.js"></script>
    <script>
      if ($("#lineChart").length) {
        $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
          type: "line", height: "70", width: "100%", lineWidth: "2",
          lineColor: "#17e6b0", fillColor: "rgba(23, 230, 176, 0.14)",
        });
      }
      if ($("#lineChart2").length) {
        $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
          type: "line", height: "70", width: "100%", lineWidth: "2",
          lineColor: "#ff5d7a", fillColor: "rgba(255, 93, 122, .14)",
        });
      }
      if ($("#lineChart3").length) {
        $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
          type: "line", height: "70", width: "100%", lineWidth: "2",
          lineColor: "#ffb020", fillColor: "rgba(255, 176, 32, .14)",
        });
      }

      // Mobile sidebar toggle
      var toggleBtn = document.getElementById('sidebarToggle');
      var sidebar = document.getElementById('left-panel');
      var overlay = document.getElementById('sidebarOverlay');
      if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', function () {
          sidebar.classList.toggle('show');
          overlay.classList.toggle('show');
        });
        overlay.addEventListener('click', function () {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        });
      }
    </script>
</body>

</html>
