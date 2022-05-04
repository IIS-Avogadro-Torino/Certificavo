"use strict";

let windowLoc= location.pathname.split('/');
windowLoc = windowLoc[windowLoc.length - 1];
let getParams = new URL(location.href).searchParams;

switch(windowLoc){    

  case 'logs.php':

    document.querySelector('.back-arrow').onclick = () =>{
      location.href = 'index.php';
    }

  break;

  case 'reCertiByDB.php':

    document.querySelector('.back-arrow').onclick = () =>{
      location.href = 'templateWizard.php?redirect=3';
    }

  break;

  case 'setMail.php':

    document.querySelector('.back-arrow').onclick = () =>{
      location.href = 'index.php';
    }

  break;

  case 'templateWizard.php': 

    document.querySelector(".back-arrow").onclick = () =>{
      location.href = 'setMail.php?redirect=' + getParams.get('redirect');
    }

  break;

  case 'uploadData.php':

    document.querySelector(".back-arrow").onclick = () =>{
      location.href = "templateWizard.php?redirect=1";
    }

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
    
        document.querySelector("#hideFields").innerHTML=`<ul class="hideFields"> 
                <li style="display: none;">
                    <input type="checkbox" name="hiddenFields[]" value="">
                    <h5></h5>
                </li>
            </ul>`;

        printFieldsNames(document.querySelector("#uploadButton").files[0]);
    })
    
  break;

  case 'uploadDataForm.php': 

    document.querySelector(".back-arrow").onclick = () =>{
      location.href = "templateWizard.php?redirect=2";
    }

    document.querySelector("#addField").onclick = () =>{

      const ele = document.querySelectorAll(".input-text");
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

      document.querySelector(".container").appendChild(cpyEle);
    }

    document.querySelector(".container").addEventListener("DOMNodeInserted", () =>{
      document.querySelectorAll(".delete-btn").forEach(ele => {
          ele.onclick = () =>{
              const parentParent = ele.parentElement.parentElement;
              const parent = ele.parentElement;
              parentParent.removeChild(parent);
          }
      }); 
    });

  break;
}