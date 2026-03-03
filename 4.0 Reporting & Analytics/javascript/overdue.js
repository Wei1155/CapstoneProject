function isOverdue(dateStr){

    let today = new Date()
    let due = new Date(dateStr)

    return today > due
}