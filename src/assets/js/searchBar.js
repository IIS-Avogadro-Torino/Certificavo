function searchBar(item)
{
    const list = document.querySelector("ul") === null ? document.querySelector(".send-certificate-container") : document.querySelector("ul");
   
    for(let i = 0;i< list.children.length;i++)
        if(list.children[i].innerText.toUpperCase().indexOf(item.toUpperCase()) === -1)
            list.children[i].style.display = "none";
        else
            list.children[i].style.display = "flex";
}   

document.querySelector(".search-bar").addEventListener("keyup",() => searchBar(document.querySelector(".search-bar").value));
