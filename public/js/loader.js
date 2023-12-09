
function require(file) {
    var script = document.createElement("script");
    script.src = file;
    script.onreadystatechange = function () {
      if (script.readyState === "loaded" || script.readyState === "complete") {
        script.onreadystatechange = null;
      }
    };
  
    document.getElementsByTagName("head")[0].appendChild(script);
}

fetch('../pages/version.txt')
.then((res) => res.text())
.then((outJson) => {
    window.codeVersion = outJson;
    require('../js/bundle.js?v=' + outJson);
});