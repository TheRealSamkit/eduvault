document.addEventListener("DOMContentLoaded", function () {
  var reportModal = document.getElementById("exampleModal");
  reportModal.addEventListener("show.bs.modal", function (event) {
    var button = event.relatedTarget;
    var reportId = button.getAttribute("data-report-id");
    var reportTitle = button.getAttribute("data-report-title");
    var reportContentType = button.getAttribute("data-content-type");
    reportModal.querySelector("#modalReportedId").value = reportId;
    reportModal.querySelector("#modalReportedContent").value =
      reportContentType;
    reportModal.querySelector("#reportModalLabel").textContent =
      "Report " + reportTitle;
  });
});
