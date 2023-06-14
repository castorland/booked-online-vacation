window.addEventListener('load', (event) => {
    console.log("It works!");
    document.addEventListener('booked-on-new-app', function(){
        console.log("I want this to appear after the modal has opened!");
    });
})