document.querySelectorAll(".component-slider__card").forEach(ele =>{
    ele.onclick = () =>{
        if(ele.querySelector("input[type='radio']"))
            ele.querySelector("input[type='radio']").click();
        else
            ele.querySelector("input[type='checkbox']").click();
    }
});

function printFieldsNames(file)
{
    let reader = new FileReader();
    reader.readAsText(file);
    let contents;
    reader.onload = function(e) {
        contents = e.target.result;
        let separator = findSeparator(contents.split("\n")[0].toUpperCase());
        fieldsNames = contents.split("\n")[0].toUpperCase().split(separator);
        addHiddenFields(fieldsNames);
        let topText = document.createElement("h4");
        topText.innerHTML = "Numero di righe trovate: "+(contents.split("\n").length-1);
        let title = document.querySelector("#hideFields h4");
        document.querySelector("#hideFields").insertBefore(topText,title);
    }; 
    
}

function addHiddenFields(fieldsNames)
{
    let topText = document.createElement("h4");
    topText.innerHTML = "Scegli campi opzionali da non stampare (opzionale):";
    let listContainer = document.querySelector("#hideFields");
    const list =  document.querySelector("#hideFields > ul");
    listContainer.insertBefore(topText,list);

    const hideEntry = document.querySelector("#hideFields > ul > li");
    fieldsNames.forEach(ele =>{
        ele = ele.trim();
        if(ele === "COGNOME" || ele === "NOME" || ele === "NOME CORSO" || ele === "CORSO" || ele === "DATA" || ele === "E-MAIL" || ele === "ORE" || ele === "NUMERO ORE")
            return;
        let newEntry = hideEntry.cloneNode(true);
        newEntry.querySelector("h5").innerText = ele;
        newEntry.querySelector("h5").style.margin = "0 0 0 5px";
        newEntry.style.display = "flex";
        newEntry.style.marginBottom = "10px";
        newEntry.querySelector("input[type='checkbox']").value = ele;
        list.append(newEntry);
    });
}

function findSeparator(text)
{
    let separators = {
        "," : 0,
        ";" : 0
    };

    for(let i = 0;i < text.length;i++)
        if(Object.keys(separators).includes(text[i]))
        {
            if(text[i] ===",")
                separators[","]++;
            else
                separators[";"]++;
        }         

    if(separators[","] > separators[";"])
        return ",";
    else
        return ";";
}