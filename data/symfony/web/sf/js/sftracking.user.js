// ==UserScript==
// @name          sfStracking
// @namespace     http://www.sensio.com/projects/greasemonkey/
// @description   tracking module for SymFony framework
// @include       *
// ==/UserScript==

(function() {
  var baseHref = window.location.protocol + '//' + window.location.host;

  addGlobalStyle('.sfStatsNumber { padding: 0; margin: 0; position: relative; z-index: 999; overflow: hidden }');
  addGlobalStyle('.sfStatsPercentOK { height: 8px; background-color: #3f6; border: 1px solid #666; border-right: none }');
  addGlobalStyle('.sfStatsPercentKO { height: 8px; background-color: #eee; border: 1px solid #666; border-left: none; border-right: none }');
  addGlobalStyle('.sfStatsPercent { height: 8px; padding-left: 1px; text-align: center; font-family: verdana; font-size: 7px; background-color: #ffa; border: 1px solid #666 }');

  GM_xmlhttpRequest(
  {
    method: 'GET',
    url: baseHref + '/get_stats.php?uri=' + window.location.pathname,
    onload: function(responseDetails)
    {
      if (responseDetails.status == 200)
      {
        // we parse response from server
        var parser = new DOMParser();
        var xmlDoc = parser.parseFromString(responseDetails.responseText, "application/xml");
        var uris = xmlDoc.getElementsByTagName('uri');
        var uri;
        var total = 0;
        var stats = new Array();
        for (var i = 0; i < uris.length; i++)
        {
          uri = uris[i];
          stats[uri.getAttribute('uri')] = parseInt(uri.getAttribute('count'));
          total += parseInt(uri.getAttribute('count'));
        }

        // we add some information below each link
        var allLinks, thisLink, count, newElement, newElementPercent, newElementPercentOK, newElementPercentKO, color, percent;
        var alreadySeenUrl = new Array();
        allLinks = document.evaluate('//a[@href]', document, null, XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null);
        for (var i = 0; i < allLinks.snapshotLength; i++)
        {
          thisLink = allLinks.snapshotItem(i);
          count = stats[thisLink.getAttribute('href')];
          if (count > 0)
          {
            percent = parseFloat(count / total);

            newElement = document.createElement('span');
            newElement.className = 'sfStatsNumber';

            if (percent > 0.1)
            {
              newElementPercentOK = document.createElement('img');
              newElementPercentOK.className = 'sfStatsPercentOK';
              newElementPercentOK.width = parseInt(percent * 100 / 4);
              newElementPercentOK.align = 'absmiddle';
              newElementPercentOK.src = '/sf/images/sf_stats/spacer.gif';

              newElementPercentKO = document.createElement('img');
              newElementPercentKO.className = 'sfStatsPercentKO';
              newElementPercentKO.width = parseInt((100 - percent * 100) / 4);
              newElementPercentKO.align = 'absmiddle';
              newElementPercentKO.src = '/sf/images/sf_stats/spacer.gif';

              newElement.appendChild(newElementPercentOK);
              newElement.appendChild(newElementPercentKO);
            }

            newElementPercent = document.createElement('span');
            newElementPercent.className = 'sfStatsPercent';

            if (alreadySeenUrl[thisLink.getAttribute('href')] == 1)
              newElementPercent.style.backgroundColor = '#ccc';
            else
              newElementPercent.style.backgroundColor = '#fff';

            if (percent < 0.1)
              newElementPercent.innerHTML = '<1%';
            else
              newElementPercent.innerHTML = parseInt(percent * 100) + '%';

            newElement.appendChild(newElementPercent);

            thisLink.parentNode.insertBefore(newElement, thisLink.nextSibling);
            alreadySeenUrl[thisLink.getAttribute('href')] = 1;
          }
        }
      }
    }
  });

  function addGlobalStyle(css)
  {
    var head, style;
    head = document.getElementsByTagName('head')[0];
    if (!head) { return; }
    style = document.createElement('style');
    style.type = 'text/css';
    style.innerHTML = css;
    head.appendChild(style);
  }

   // Original JavaScript code by Duncan Crombie: dcrombie@chirp.com.au
   // Please acknowledge use of this code by including this header.

   // CONSTANTS
  var separator = ",";  // use comma as 000's separator
  var decpoint = ".";  // use period as decimal point
  var percent = "%";
  var currency = "$";  // use dollar sign for currency

  function formatNumber(number, format, print) {  // use: formatNumber(number, "format")
    if (print) document.write("formatNumber(" + number + ", \"" + format + "\")<br>");

    if (number - 0 != number) return null;  // if number is NaN return null
    var useSeparator = format.indexOf(separator) != -1;  // use separators in number
    var usePercent = format.indexOf(percent) != -1;  // convert output to percentage
    var useCurrency = format.indexOf(currency) != -1;  // use currency format
    var isNegative = (number < 0);
    number = Math.abs (number);
    if (usePercent) number *= 100;
    format = strip(format, separator + percent + currency);  // remove key characters
    number = "" + number;  // convert number input to string

     // split input value into LHS and RHS using decpoint as divider
    var dec = number.indexOf(decpoint) != -1;
    var nleftEnd = (dec) ? number.substring(0, number.indexOf(".")) : number;
    var nrightEnd = (dec) ? number.substring(number.indexOf(".") + 1) : "";

     // split format string into LHS and RHS using decpoint as divider
    dec = format.indexOf(decpoint) != -1;
    var sleftEnd = (dec) ? format.substring(0, format.indexOf(".")) : format;
    var srightEnd = (dec) ? format.substring(format.indexOf(".") + 1) : "";

     // adjust decimal places by cropping or adding zeros to LHS of number
    if (srightEnd.length < nrightEnd.length) {
      var nextChar = nrightEnd.charAt(srightEnd.length) - 0;
      nrightEnd = nrightEnd.substring(0, srightEnd.length);
      if (nextChar >= 5) nrightEnd = "" + ((nrightEnd - 0) + 1);  // round up

 // patch provided by Patti Marcoux 1999/08/06
      while (srightEnd.length > nrightEnd.length) {
        nrightEnd = "0" + nrightEnd;
      }

      if (srightEnd.length < nrightEnd.length) {
        nrightEnd = nrightEnd.substring(1);
        nleftEnd = (nleftEnd - 0) + 1;
      }
    } else {
      for (var i=nrightEnd.length; srightEnd.length > nrightEnd.length; i++) {
        if (srightEnd.charAt(i) == "0") nrightEnd += "0";  // append zero to RHS of number
        else break;
      }
    }

     // adjust leading zeros
    sleftEnd = strip(sleftEnd, "#");  // remove hashes from LHS of format
    while (sleftEnd.length > nleftEnd.length) {
      nleftEnd = "0" + nleftEnd;  // prepend zero to LHS of number
    }

    if (useSeparator) nleftEnd = separate(nleftEnd, separator);  // add separator
    var output = nleftEnd + ((nrightEnd != "") ? "." + nrightEnd : "");  // combine parts
    output = ((useCurrency) ? currency : "") + output + ((usePercent) ? percent : "");
    if (isNegative) {
      // patch suggested by Tom Denn 25/4/2001
      output = (useCurrency) ? "(" + output + ")" : "-" + output;
    }
    return output;
  }

  function strip(input, chars) {  // strip all characters in 'chars' from input
    var output = "";  // initialise output string
    for (var i=0; i < input.length; i++)
      if (chars.indexOf(input.charAt(i)) == -1)
        output += input.charAt(i);
    return output;
  }

  function separate(input, separator) {  // format input using 'separator' to mark 000's
    input = "" + input;
    var output = "";  // initialise output string
    for (var i=0; i < input.length; i++) {
      if (i != 0 && (input.length - i) % 3 == 0) output += separator;
      output += input.charAt(i);
    }
    return output;
  }

})();

