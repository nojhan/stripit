var actual_id = 0;

/**
 * Create an Ajax instance
 *
 * @return  XMLHttpRequest Object     The XMLHttpRequest object 
 */
function instance_ajax()
{
  var xhr;
  
  try {
    xhr = new ActiveXObject('Msxml2.XMLHTTP');
  } catch (e) {
    try {
      xhr = new ActiveXObject('Microsoft.XMLHTTP');
    } catch (e2) {
      try {
        xhr = new XMLHttpRequest();
      } catch (e3) {
        xhr = false;
      }
    }
  }

  return xhr;
}


function getStrip(link)
{
  var xhr = instance_ajax();
  var page = link.href + '&ajax=1';
  
  xhr.onreadystatechange = function() {
    if(xhr.readyState == 4 && xhr.status == 200) {
      var docXml, item, nb_item, i, id, value, select, icone;
      
      docXml  = xhr.responseXML.documentElement;
      item    = docXml.getElementsByTagName('item');
      nb_item = item.length;
      for(i = 0; i < nb_item; i++){
        // image url
        id = item[i].getAttribute('id');
        value = getNodeValue(item[i]);
        switch (id) {
          case 'png':
            if (idExist('strip')) document.getElementById('strip').src = value;
            break;
          case 'text':
            if (idExist('strip')) document.getElementById('strip').alt = value;
            break;
          case 'title':
            if (idExist('title')) document.getElementById('title').innerHTML = '&laquo;&nbsp;' + value + '&nbsp;&raquo;';
            break;
          case 'description':
            if (idExist('description')) document.getElementById('description').innerHTML = value;
            break;
          case 'comments':
            if (idExist('comments')) document.getElementById('comments').innerHTML = value;
            break;
          case 'author':
            if (idExist('author')) document.getElementById('author').innerHTML = '&copy; ' + value;
            break;
          case 'date':
            if (idExist('date')) document.getElementById('date').innerHTML = value;
            break;
          case 'license':
            if (idExist('link_license')) {
              document.getElementById('link_license').innerHTML = value;
              document.getElementById('link_license').href = value;
            }
            break;
          case 'source':
            if (idExist('link_source')) document.getElementById('link_source').href = value;
            break;
          case 'navfirst':
            if (idExist('t_navfirst')) document.getElementById('t_navfirst').href = value;
            if (idExist('b_navfirst')) document.getElementById('b_navfirst').href = value;
            break;
          case 'navprev':
            if (idExist('t_navprev')) document.getElementById('t_navprev').href = value;
            if (idExist('b_navprev')) document.getElementById('b_navprev').href = value;
            break;
          case 'navnext':
            if (idExist('t_navnext')) document.getElementById('t_navnext').href = value;
            if (idExist('b_navnext')) document.getElementById('b_navnext').href = value;
            break;
          case 'navlast':
            if (idExist('t_navlast')) document.getElementById('t_navlast').href = value;
            if (idExist('b_navlast')) document.getElementById('b_navlast').href = value;
            break;
          case 'navgallery':
            if (idExist('t_navgallery')) document.getElementById('t_navgallery').href = value;
            if (idExist('b_navgallery')) document.getElementById('b_navgallery').href = value;
            break;
          case 'nav_forum_post':
            if (idExist('nav_forum_post')) document.getElementById('nav_forum_post').href = value;
            break;
          case 'nav_forum_view':
            if (idExist('nav_forum_view')) document.getElementById('nav_forum_view').href = value;
            break;
        }
      }
    }
  }
  
  xhr.open('GET', page, true);
  xhr.send(null);
}

/**
 * Fonction permettant de récupérer la valeur d'un noeud XML quelque soit le navigateur
 *
 * @param   DOM Object    node      Le noeud sur lequel on travaille
 */
function getNodeValue(node)
{
  var return_value = null;
  
  return_value = node.textContent;
  if(return_value == undefined){
    // On a à faire à un IE, donc on fait le boulot de Microsoft : on le debugge
    return_value = node.firstChild.nodeValue;
  }
  
  return return_value;
}


/**
 * Vérifie l'existance d'un élément identifié par un identifiant
 *
 * @param   string   id   L'identifiant à vérifier
 * @return  bool          Vrai si l'élément existe, Faux sinon
 */
function idExist(id)
{
  if (document.getElementById(id)) {
    return true;
  } else {
    return false;
  }
}
