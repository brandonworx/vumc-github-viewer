//CONNECTOR JS

//API DATA
const apipath = "/api/index.php";

//FUNCTIONS
/**
 * @name api
 * @description Executes an api request at the target apipath using the given api command
 * @param {string} command The api command to execute
 * @param {string} params The api command to execute
 * @returns {json} The api request as JSON data
 */
function api(command, params){
    return new Promise(function (resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.responseType = "json";
        xhr.open("POST", apipath, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if ( this.status >= 200 && this.status < 300 ){
                resolve(xhr.response);
            }
            else{
                reject({
                    status: this.status,
                    statusText: xhr.statusText
                });
            }
        }
        xhr.onerror = function(){
            reject({
                status: this.status,
                statusText: xhr.statusText
            });
        }
        xhr.send("command="+command+"&"+params);
    });
}