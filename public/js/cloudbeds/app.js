cb = {};

(function(cb) {
    
    var API = {
        get: () => {
            return new Promise((resolve, reject) => {

                var xhr = new XMLHttpRequest();
                xhr.open('GET', '/api/interval', true);
                xhr.onreadystatechange = () => {
                    if(xhr.readyState != 4)
                    {
                        return;
                    }
                    if(xhr.status != 200)
                    {
                        reject(xhr.response);
                        return;
                    }
                    var type = xhr.getResponseHeader('Content-Type');
                    if(type == 'application/json')
                    {
                        resolve(JSON.parse(xhr.response));
                        return;
                    }
                    resolve(xhr.response);
                }
                xhr.send();

            });
        },
        post: (start, end, price) => {
            return new Promise((resolve, reject) => {

                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/api/interval', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = () => {
                    if(xhr.readyState != 4)
                    {
                        return;
                    }
                    if(xhr.status != 200)
                    {
                        reject(xhr.response);
                        return;
                    }
                    var type = xhr.getResponseHeader('Content-Type');
                    if(type == 'application/json')
                    {
                        resolve(JSON.parse(xhr.response));
                        return;
                    }
                    resolve(xhr.response);
                }
                var body = 'start='+encodeURIComponent(start)
                    + '&end='+encodeURIComponent(end)
                    + '&price='+encodeURIComponent(price);
                xhr.send(body);

            });
        },
        reset: () => {
            return new Promise((resolve, reject) => {

                var xhr = new XMLHttpRequest();
                xhr.open('DELETE', '/api/interval/reset', true);
                xhr.onreadystatechange = () => {
                    if(xhr.readyState != 4)
                    {
                        return;
                    }
                    if(xhr.status != 200)
                    {
                        reject(xhr.response);
                        return;
                    }
                    var type = xhr.getResponseHeader('Content-Type');
                    if(type == 'application/json')
                    {
                        resolve(JSON.parse(xhr.response));
                        return;
                    }
                    resolve(xhr.response);
                }
                xhr.send();

            });
        }
    };

    var Actions = {        
        resetList: () => {
            API.get()
                .then((response) => {
                    if(response.success)
                    {
                        var innerHtml = '<form>';
                        for(i in response.intervals)
                        {
                            var interval = response.intervals[i];
                            innerHtml += '<input type="hidden" name="id" value="'+interval.id+'">';
                            innerHtml += '<input type="text" placeholder="Start: YYYY-MM-DD" name="start" value="'+interval.start+'">';
                            innerHtml += '<input type="text" placeholder="End: YYYY-MM-DD" name="end" value="'+interval.end+'">';
                            innerHtml += '<input type="text" placeholder="price" name="price" value="'+interval.price+'">'
                        }
                        innerHtml += '</form>'
                        document.getElementById("intervals").innerHTML = innerHtml;
                    }
                })
                .catch((response) => {
                    console.log('error: '.response);
                })
        },
        addButtons: () => {
            var innerHtml = '<button onclick="cb.clickReset()">Reset</button>';
            innerHtml += '<button onclick="cb.clickRefresh()">Refresh</button>';
            document.getElementById("actions").innerHTML = innerHtml;
        },
        clearForm: () => {
            var innerHtml = '<form>';
            innerHtml += '<input type="text" placeholder="Start: YYYY-MM-DD" name="start" value="">';
            innerHtml += '<input type="text" placeholder="End: YYYY-MM-DD" name="end" value="">';
            innerHtml += '<input type="text" placeholder="price" name="price" value="">'
            innerHtml += '<button onclick="cb.addInterval(this.form)" type="button">Add</button>'            
            innerHtml += '</form>';
            document.getElementById("form").innerHTML = innerHtml;
        }
    }

    cb.clickReset = () => {
        API.reset()
            .then((response) => {  
                if(response.success)
                {
                    Actions.resetList();
                }
            })
            .catch((response) => { console.log(response)});
    };

    cb.clickRefresh = () => {
        Actions.resetList();
    };

    cb.addInterval = (form) => {
        var start = form[0].value;
        var end = form[1].value;
        var price = form[2].value;
        API.post(start, end, price)
            .then((response) => {
                if(response.success)
                {
                    Actions.clearForm();
                    Actions.resetList();
                }
            })
            .catch((response) => {console.log(response)});
    };

    window.addEventListener('load', () => {
        Actions.resetList();
        Actions.addButtons();
        Actions.clearForm();
    });

})(cb);
