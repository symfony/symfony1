// ==UserScript==
// @name          FindMissingElements
// @namespace     http://www.sensio.com/projects/greasemonkey/
// @description   display curent page missing elements (images, css, js)
// @include       *
// ==/UserScript==

//cf. http://www.xs4all.nl/~jlpoutre/BoT/Javascript/RSSpanel/rsspanel.user.js for drag & drop and many other tricks

(function() {
  var alreadySeenUrl = new Object();
  var missingElements = new Array();
  var baseHref = window.location.protocol + '//' + window.location.host;

	var	BACKGROUND = "#ffc",
			TEXT = "#000",
			BORDER = "orange",
			TITLE_BACKGROUND = "orange",
			TITLE_BORDER = "#ffc",
			TITLE_TEXT = "#fff",
			OPACITY	= "0.85";

  addLoadEvent(render);

  // cf. http://simon.incutio.com/archive/2004/05/26/addLoadEvent
  function addLoadEvent(func)
  {
    var oldonload = window.onload;
    if (typeof window.onload != 'function') {
      window.onload = func;
    } else {
      window.onload = function() {
        oldonload();
        func();
      };
    }
  }

	function render()
	{
    sfFindMissingImages();
    sfFindMissingCss();
    sfFindMissingJs();

    if (missingElements.length)
    {
      BACKGROUND = "#ffc";
  		BORDER = "orange";
  		TITLE_BACKGROUND = "orange";
  		TITLE_BORDER = "#ffc";
    }
    else
    {
      BACKGROUND = "#ffc";
  		BORDER = "lightgreen";
  		TITLE_BACKGROUND = "lightgreen";
  		TITLE_BORDER = "#ffc";
    }

		var expander = function() {
			// closed state
			if (box.style.height == "15px")
			{
				box.style.height = "auto";
				box.style.overflow = "auto";
				close.style.right = "13px";
				open.style.right = "27px";
				open.firstChild.nodeValue = "<";
			}
			else
			{
				box.style.height = "15px";
				box.style.overflow = "hidden";
				close.style.right = "3px";
				open.style.right = "17px";
				open.firstChild.nodeValue = ">";
			}
		};

		var box = document.createElement("div");
		dom_setStyle(box, "position:fixed;z-index:998;top:1px;left:1px;background-color:" + BACKGROUND + ";border:1px solid " + BORDER + ";padding:4px;text-align:left;opacity:" + OPACITY + ";font:8pt sans-serif;overflow:hidden;width:300px;height:15px;max-height:100%;margin-bottom:15px;");

		var title = document.createElement("div");
		title.setAttribute("title","Double-Click title to expand/collapse");
		dom_setStyle(title, "position:absolute;top:1px;left:1px;z-index:999;background-color:" + TITLE_BACKGROUND + ";border:1px solid " + TITLE_BORDER + ";padding:4px;text-align:left;font:8pt sans-serif;width:296px;height:11px;margin-bottom:15px;cursor:move;font-weight:bold;color:" + TITLE_TEXT + ";");
		title.appendChild(document.createTextNode((missingElements.length) ? missingElements.length + ' missing element(s)' : 'no missing element'));

		var close = document.createElement("div");
		dom_setStyle(close, "position:absolute;top:3px;right:3px;width:10px;height:10px;border:1px solid " + TITLE_BORDER + ";line-height:8px;text-align:center;cursor:pointer;");
		close.setAttribute("title","Click to close panel");
		close.onclick = function() { this.parentNode.parentNode.style.display = "none"; };
		close.appendChild(document.createTextNode("x"));

		var open  = document.createElement("div");
		dom_setStyle(open, "position:absolute;top:3px;right:17px;width:10px;height:10px;border:1px solid " + TITLE_BORDER + ";line-height:8px;text-align:center;cursor:pointer;");
		open.setAttribute("title","Click to expand/collapse");
		open.appendChild(document.createTextNode(">"));
		open.onclick = expander;

    if (missingElements.length)
    {
  		title.appendChild(open);
    }

    title.appendChild(close);
		box.appendChild(title);

		var ul = document.createElement("ul");
		dom_setStyle(ul, "padding-left: 5px; padding-top: 20px; margin-bottom: 20px; list-style-type: none");

		for (var i = 0; i < missingElements.length; i++)
		{
			var li = document.createElement("li");
			dom_setStyle(li, "color:" + TEXT + ";");
      text = missingElements[i];
      //we strip base domain name to short text a little
      re = new RegExp(baseHref, 'i');
      li.innerHTML = text.replace(re, '');
			ul.appendChild(li);
		}

		var div = document.createElement("div");
		div.appendChild(ul);
		title.ondblclick = expander;
		box.appendChild(div);
		dom_getElements(document, "body")[0].appendChild(box);

		Drag.init(title, box);
	}

	function dom_createLink(url, txt, title)
	{
		var a  = document.createElement("a");
		a.setAttribute("href", url);
		dom_setStyle(a, "color:"+TEXT+";");
		if (title) a.setAttribute("title", title);
		a.appendChild(document.createTextNode(txt));
		return a;
	}

	function dom_getElements(node, elt) {
		var list = node.getElementsByTagName(elt);
		return (list.length) ? list : node.getElementsByTagNameNS("*", elt); 
	}

  // Finds all the Missing images on the page
  // based on webdeveloper_findMissingImages() => webdeveloper/images.js
  function sfFindMissingImages()
  {
    var backgroundImage = null;
    var MissingURLs = null;
    var element = null;
    var image = null;
    var imageList = null;
  
    MissingURLs = "";
    imageList = document.getElementsByTagName("img");
  
    // Loop through all the images
    for (var j = 0; j < imageList.length; j++)
    {
      image = imageList[j];
  
      // If the image is Missing
      if (!image.naturalWidth && !alreadySeenUrl[image.src])
      {
        missingElements.push('[image]&nbsp;' + image.src);
      }
      alreadySeenUrl[image.src] = 1;
    }
  
    // While the tree walker has more nodes
    const treeWalker = document.createTreeWalker(document.body, NodeFilter.SHOW_ELEMENT, null, false);
    while ((element = treeWalker.nextNode()) != null)
    {
      backgroundImage = element.ownerDocument.defaultView.getComputedStyle(element, "").getPropertyCSSValue("background-image");
  
      // If this element has a background image and it is a URL
      if (backgroundImage && backgroundImage.primitiveType == CSSPrimitiveValue.CSS_URI)
      {
        image = new Image();
        image.src = backgroundImage.getStringValue();
  
        // If the image is Missing
        if (!image.naturalWidth && !alreadySeenUrl[image.src])
        {
          missingElements.push('[image]&nbsp;' + image.src);
        }
        alreadySeenUrl[image.src] = 1;
      }
    }
  }
  
  function sfFindMissingCss()
  {
    const request = new XMLHttpRequest();
  
    var ownerNode = null;
    var styleSheet = null;
    var styleSheetHref = null;
    var styleSheetList = document.styleSheets;
  
    // Loop through the style sheets
    for (var i = 0; i < styleSheetList.length; i++)
    {
      styleSheet = styleSheetList[i];
      ownerNode = styleSheet.ownerNode;
      styleSheetHref = styleSheet.href;
  
      // If this is not an inline style sheet, a rule from the browser or an alternate style sheet
      if (styleSheetHref != document.documentURI && styleSheetHref.indexOf("resource://") != 0 && styleSheetHref.indexOf("about:PreferenceStyleSheet") != 0 && styleSheetHref.indexOf("jar:file://") != 0 && styleSheetHref.indexOf("chrome://") != 0 && (!ownerNode || ownerNode.nodeType == 7 || !ownerNode.hasAttribute("rel") || ownerNode.getAttribute("rel").toLowerCase() != "alternate stylesheet"))
      {
        request.open("head", styleSheetHref, false);
        request.send("");
        if (request.status != 200 && !alreadySeenUrl[styleSheetHref])
        {
          missingElements.push('[css]&nbsp;' + styleSheetHref);
        }
        alreadySeenUrl[styleSheetHref] = 1;
      }
    }
  }
  
  function sfFindMissingJs()
  {
    const request = new XMLHttpRequest();
  
    var scriptList = document.getElementsByTagName("script");
  
    // Loop through the scripts
    for(var j = 0; j < scriptList.length; j++)
    {
      scriptElement = scriptList[j];
      scriptSource  = scriptElement.src;
  
      // If the script is external
      if(scriptSource)
      {
        request.open("head", scriptSource, false);
        request.send("");
  
        if (request.status != 200 && !alreadySeenUrl[scriptSource])
        {
          missingElements.push('[js]&nbsp;' + scriptSource);
        }
        alreadySeenUrl[scriptSource] = 1;
      }
    }
  }
  
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

	function dom_setStyle(elt, str) {
		elt.setAttribute("style", str);
		// for MSIE:
		if (elt.style.setAttribute) {
			elt.style.setAttribute("cssText", str, 0);
			// positioning for MSIE:
			if (elt.style.position == "fixed") {
				elt.style.position = "absolute";
			}
		}
	}

/**************************************************
 * The code below came directly from 
 *   http://www.youngpup.net/
 * dom-drag.js
 * 09.25.2001
 * www.youngpup.net
 **************************************************
 * 10.28.2001 - fixed minor bug where events
 * sometimes fired off the handle, not the root.
 **************************************************/

var Drag = {

	obj : null,

	init : function(o, oRoot, minX, maxX, minY, maxY, bSwapHorzRef,
bSwapVertRef, fXMapper, fYMapper)
	{
		o.onmousedown   = Drag.start;

		o.hmode		 = bSwapHorzRef ? false : true ;
		o.vmode		 = bSwapVertRef ? false : true ;

		o.root = oRoot && oRoot != null ? oRoot : o ;

		if (o.hmode  && isNaN(parseInt(o.root.style.left  )))
o.root.style.left   = "0px";
		if (o.vmode  && isNaN(parseInt(o.root.style.top   )))
o.root.style.top	= "0px";
		if (!o.hmode && isNaN(parseInt(o.root.style.right )))
o.root.style.right  = "0px";
		if (!o.vmode && isNaN(parseInt(o.root.style.bottom)))
o.root.style.bottom = "0px";

		o.minX  = typeof minX != 'undefined' ? minX : null;
		o.minY  = typeof minY != 'undefined' ? minY : null;
		o.maxX  = typeof maxX != 'undefined' ? maxX : null;
		o.maxY  = typeof maxY != 'undefined' ? maxY : null;

		o.xMapper = fXMapper ? fXMapper : null;
		o.yMapper = fYMapper ? fYMapper : null;

		o.root.onDragStart  = new Function();
		o.root.onDragEnd	= new Function();
		o.root.onDrag	   = new Function();
	},

	start : function(e)
	{
		var o = Drag.obj = this;
		e = Drag.fixE(e);
		var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
		var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
		o.root.onDragStart(x, y);

		o.lastMouseX	= e.clientX;
		o.lastMouseY	= e.clientY;

		if (o.hmode) {
			if (o.minX != null) o.minMouseX = e.clientX - x + o.minX;
			if (o.maxX != null) o.maxMouseX = o.minMouseX + o.maxX - o.minX;
		} else {
			if (o.minX != null) o.maxMouseX = -o.minX + e.clientX + x;
			if (o.maxX != null) o.minMouseX = -o.maxX + e.clientX + x;
		}

		if (o.vmode) {
			if (o.minY != null) o.minMouseY = e.clientY - y + o.minY;
			if (o.maxY != null) o.maxMouseY = o.minMouseY + o.maxY - o.minY;
		} else {
			if (o.minY != null) o.maxMouseY = -o.minY + e.clientY + y;
			if (o.maxY != null) o.minMouseY = -o.maxY + e.clientY + y;
		}

		document.onmousemove	= Drag.drag;
		document.onmouseup	  = Drag.end;

		return false;
	},

	drag : function(e)
	{
		e = Drag.fixE(e);
		var o = Drag.obj;

		var ey  = e.clientY;
		var ex  = e.clientX;
		var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
		var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
		var nx, ny;

		if (o.minX != null) ex = o.hmode ? Math.max(ex, o.minMouseX) :
Math.min(ex, o.maxMouseX);
		if (o.maxX != null) ex = o.hmode ? Math.min(ex, o.maxMouseX) :
Math.max(ex, o.minMouseX);
		if (o.minY != null) ey = o.vmode ? Math.max(ey, o.minMouseY) :
Math.min(ey, o.maxMouseY);
		if (o.maxY != null) ey = o.vmode ? Math.min(ey, o.maxMouseY) :
Math.max(ey, o.minMouseY);

		nx = x + ((ex - o.lastMouseX) * (o.hmode ? 1 : -1));
		ny = y + ((ey - o.lastMouseY) * (o.vmode ? 1 : -1));

		if (o.xMapper) nx = o.xMapper(y)
		else if (o.yMapper) ny = o.yMapper(x)

		Drag.obj.root.style[o.hmode ? "left" : "right"] = nx + "px";
		Drag.obj.root.style[o.vmode ? "top" : "bottom"] = ny + "px";
		Drag.obj.lastMouseX = ex;
		Drag.obj.lastMouseY = ey;

		Drag.obj.root.onDrag(nx, ny);
		return false;
	},

	end : function()
	{
		document.onmousemove = null;
		document.onmouseup   = null;
		Drag.obj.root.onDragEnd(	parseInt(Drag.obj.root.style[Drag.obj.hmode
? "left" : "right"]),
									parseInt(Drag.obj.root.style[Drag.obj.vmode ? "top" : "bottom"]));
		Drag.obj = null;
	},

	fixE : function(e)
	{
		if (typeof e == 'undefined') e = window.event;
		if (typeof e.layerX == 'undefined') e.layerX = e.offsetX;
		if (typeof e.layerY == 'undefined') e.layerY = e.offsetY;
		return e;
	}
};

/* End drag */

})();

