document.querySelectorAll(".certificate-selection").forEach(ele =>{
    ele.onclick = () =>{
        if(ele.querySelector("input[type='radio']"))
            ele.querySelector("input[type='radio']").click();
        else
            ele.querySelector("input[type='checkbox']").click();
    }
});

document.querySelector(".logo-nav").onclick = () =>{
    location.href = "index.php";
}

document.querySelector("#addField").onclick = () =>{
    const ele = document.querySelectorAll(".input-container");
    const cpyEle = ele[ele.length-1].cloneNode(true);
    const input = cpyEle.querySelector("input");
    const isOtherField = input.name.indexOf("altro");
    if(isOtherField == -1 )
    {
        input.name = "altro0";
        const newEle = document.createElement("button");
        newEle.classList = "button delete-btn";
        newEle.type = "button";
        newEle.innerHTML = "-";
        cpyEle.appendChild(newEle);
    }
    else
    {
        input.name = "altro" + (Number(input.name.slice(5)) + 1);
    }
    input.value = "";
    cpyEle.querySelector("label").innerHTML = "Altro";
   
    document.querySelector(".manual-upload-form").appendChild(cpyEle);
}
document.querySelector(".manual-upload-form").addEventListener("DOMNodeInserted", () =>{
    document.querySelectorAll(".delete-btn").forEach(ele => {
        ele.onclick = () =>{
            const parentParent = ele.parentElement.parentElement;
            const parent = ele.parentElement;
            parentParent.removeChild(parent);
        }
    }); 
});

document.querySelector("#fakeUploadButton").onclick = () =>{
    document.querySelector("#uploadButton").click();
}
document.querySelector("#uploadButton").addEventListener("change",() =>{
    const filepath = document.querySelector("#uploadButton").value;
    let splittedPath;
    if(filepath.indexOf("/") != -1)
        splittedPath = filepath.split("/");
    else
        splittedPath = filepath.split("\\");
    if(splittedPath[splittedPath.length-1].split(".")[1] != "csv")
    {
        document.querySelector("#uploadButton").value = "";
        return;
    }
    document.querySelector("#fileName").innerHTML = splittedPath[splittedPath.length-1];
})