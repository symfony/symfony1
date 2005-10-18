// global request and XML document objects
var req;

// XML container id
var xmlid = '';

// GET
function loadXMLDoc(id, url)
{
	sfXmlHttpRequest_init(id);
	req = sfXmlHttpRequest_create();
	req.open("GET", url, true);
	req.onreadystatechange = processReqChange;
	req.send(null);
}

// POST
function loadXMLDocForm(id, form)
{
	sfXmlHttpRequest_init(id);
	var queryString = "";
	for (i = 0; form.elements[i]; i++)
	{
		el = form.elements[i];
		if (el.type == undefined) continue;

		if (el.type == 'checkbox')
		{
			if (el.checked)
				queryString += "&" + el.name + "=" + el.value;
		}
		else if (el.type == 'radio')
		{
			if (el.checked)
				queryString += "&" + el.name + "=" + el.value;
		}
		else
			queryString += "&" + el.name + "=" + el.value;
	}

	queryString = queryString.substring(1, queryString.length);

	url = form.action;

	req = sfXmlHttpRequest_create();
	req.onreadystatechange = processReqChange;
	req.open("POST", url, true);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.setRequestHeader("Content-Length", queryString.length); 
	req.send(queryString);
}

// handle onreadystatechange event of req object
function processReqChange()
{
	// only if req shows "loaded"
	if (req.readyState == 4)
	{
		sfXmlHttpRequest_desinit();

		// only if "OK"
		if (req.status == 200)
		{
			div = document.getElementById(xmlid);
			div.innerHTML = '';
			div.innerHTML = req.responseText;
		}
		else
		{
			alert("There was a problem retrieving the XML data:\n" + req.statusText);
		}
	}
}

function sfXmlHttpRequest_desinit()
{
	div = document.getElementById(xmlid + 'Loading');
	div.style.display = 'none';
	div.innerHTML = '';
}

function sfXmlHttpRequest_init(id)
{
	xmlid = id;
	div = document.getElementById(xmlid + 'Loading');

	// We move loading div, so it is visible
	div.style.position = 'absolute';
	if (window.innerHeight)
		pos = window.pageYOffset
	else if (document.documentElement && document.documentElement.scrollTop)
		pos = document.documentElement.scrollTop
	else if (document.body)
		pos = document.body.scrollTop
	div.style.top = pos + 'px';

	div.style.display = 'block';
	div.innerHTML = '<img style="vertical-align: middle" src="/admin_sf/images/loading.gif" />&nbsp;Loading...';
}

function sfXmlHttpRequest_create()
{
	var xmlhttp = false;

	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	try
	{
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch (e)
	{
		try
		{
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch (e)
		{
			xmlhttp = false;
		}
	}
	@end @*/

	if (!xmlhttp && typeof XMLHttpRequest != 'undefined')
	{
		xmlhttp = new XMLHttpRequest();
	}

	return xmlhttp;
}
