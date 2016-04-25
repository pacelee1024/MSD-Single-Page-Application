var app = angular.module('QRCodeApp', []);

app.controller("formController", ['$scope', '$http', function($scope, $http){

  $scope.url = 'process.php';
  $scope.images = [];

  //validate form input and post form data by http request
  $scope.formsubmit = function(isValid){
      if(isValid){
          var strings = $scope.string.split(',');
          if (strings.length > 10) {
              alert("Should not have more than 10 codes.");
          } else {
              $http.post($scope.url, {"name":$scope.name, "email":$scope.email, "string":strings}).
                  success(function(data, status){
                    alert("You have generated codes successfully.");
                    $scope.status = status;
                    $scope.data = data;
                    $scope.result = data;
                    $scope.images = data.images;
                    $scope.zip = data.zip;
                })
                .catch(function() {
                    alert("Errors.");
                });
          }
      }
      else{
          alert("Form is not valid")
      }
  }

}]);
