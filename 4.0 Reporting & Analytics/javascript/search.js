document.getElementById("search").onkeyup = function(){

    let value = this.value.toLowerCase()
    let type = document.getElementById("filter").value

    let rows = document.querySelectorAll("tbody tr")

    rows.forEach(r => {

        let text = ""

        if(type === "name") text = r.cells[0].innerText
        if(type === "status") text = r.cells[5].innerText
        if(type === "login") text = r.cells[2].innerText

        r.style.display = text.toLowerCase().includes(value) ? "" : "none"
    })
}