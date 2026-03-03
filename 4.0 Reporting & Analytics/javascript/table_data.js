fetch("progress.txt")
.then(r => r.text())
.then(text => {

    let rows = text.split("\n")
    let tbody = document.querySelector("tbody")

    rows.forEach(line => {

        if(line === "") return

        let cols = line.split("|")

        let tr = document.createElement("tr")

        cols.forEach((c,i) => {
            let td = document.createElement("td")
            td.innerText = c
            tr.appendChild(td)
        })

        tr.onclick = function() {
            let courseName = cols[0]
            window.location = "analytics.html?course=" + courseName
        }

        tbody.appendChild(tr)
    })
})