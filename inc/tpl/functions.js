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
            document.getElementById('strip').src = value;
            break;
          case 'text':
            document.getElementById('strip').alt = value;
            break;
          case 'title':
            document.getElementById('title').innerHTML = '&laquo;&nbsp;' + value + '&nbsp;&raquo;';
            break;
          case 'description':
            document.getElementById('description').innerHTML = value;
            break;
          case 'comments':
            document.getElementById('comments').innerHTML = value;
            break;
          case 'author':
            document.getElementById('author').innerHTML = '&copy; ' + value;
            break;
          case 'date':
            document.getElementById('date').innerHTML = value;
            break;
          case 'license':
            document.getElementById('link_license').innerHTML = value;
            document.getElementById('link_license').href = value;
            break;
          case 'source':
            document.getElementById('link_source').href = value;
            break;
          case 'navfirst':
            document.getElementById('t_navfirst').href = value;
            document.getElementById('b_navfirst').href = value;
            break;
          case 'navprev':
            document.getElementById('t_navprev').href = value;
            document.getElementById('b_navprev').href = value;
            break;
          case 'navnext':
            document.getElementById('t_navnext').href = value;
            document.getElementById('b_navnext').href = value;
            break;
          case 'navlast':
            document.getElementById('t_navlast').href = value;
            document.getElementById('b_navlast').href = value;
            break;
          case 'navgallery':
            document.getElementById('t_navgallery').href = value;
            document.getElementById('b_navgallery').href = value;
            break;
          case 'nav_forum_post':
            document.getElementById('nav_forum_post').href = value;
            break;
          case 'nav_forum_view':
            document.getElementById('nav_forum_view').href = value;
            break;
        }
      }
    }
  }
  
  xhr.open('GET', page, true);
  xhr.send(null);
}

/**
 * Fonction permettant de rÃ©cupÃ¨rer la valeur d'un noeud XML quelque soit le navigateur
 *
 * @param   DOM Object    node      Le noeud sur lequel on travaille
 */
function getNodeValue(node)
{
  var return_value = null;
  
  return_value = node.textContent;
  if(return_value == undefined){
    // On a Ã  faire Ã  un IE, donc on fait le boulot de Microsoft : on le debugge
    return_value = node.firstChild.nodeValue;
  }
  
  return return_value;
}
