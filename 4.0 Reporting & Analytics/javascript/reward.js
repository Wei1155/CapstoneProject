fetch("txtdata/store.txt")
.then(r => r.text())
.then(text => {

    let tbody = document.getElementById("table")

    text.split("\n").forEach(line => {

        if(line === "") return

        let [id,name,cost,stock] = line.split("|")

        let tr = document.createElement("tr")

        tr.innerHTML =
        `<td>${name}</td>
         <td>${cost}</td>
         <td>${stock}</td>
         <td><button onclick="redeem('${id}')">Redeem</button></td>`

        tbody.appendChild(tr)
        if(points < cost) btn.disabled = true
    })
})

function redeem(id){
    alert("Run: python rewards.py redeem " + id)

}

