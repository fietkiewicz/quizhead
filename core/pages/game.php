<!doctype html>
<html data-ng-app="cwruSelfAssessment">
 <head>
  <title>Self Assessment Game</title>
  <meta charset="utf-8">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/game.css">
  <script type="text/javascript" src="js/angular.min.js"></script>
  <script type="text/javascript" src="js/game-controllers.js"></script>
 </head>
 <body data-ng-controller="CwruSelfAssessmentGame">
     <h1>Self Assessment Game</h1>
     <div id="msg">
         Logged in as <span data-ng-bind="model.user"></span>.
     </div>
     <div id="admin">
         <a href="index.php">Dashboard</a>
     </div>
     <div>
        <div id="game-left-col">
            <div data-ng-if="model.questions !== null" style="text-align:left">
                <div data-ng-if="model.questions.length > 0 && model.qn < model.questions.length">
                    <h2>Question</h2>
                    <p data-ng-bind="model.questions[model.qn].question"></p>
                    <div data-ng-repeat="choice in model.questions[model.qn].choices">
                       <input type="radio" data-ng-value="$index" data-ng-model="model.answer"/>
                       <span data-ng-bind="choice"></span>
                    </div>
                    <br/>
                    <button class="btn btn-default" data-ng-click="answerQuestion()">Submit Answer</button>
                </div>
                <div data-ng-if="model.questions.length === 0 || model.qn >= model.questions.length">
                    <h2>You Finished!</h2>
                    <p>The score on the right will keep updating until everyone finishes or the owner ends the game.</p>
                </div>
            </div>
        </div>
        <div id="game-right-col">
            <div style="text-align:left">
                <h2>Scores</h2>
                <table>
                    <tr data-ng-repeat="player in model.progress">
                        <td style="padding:4px" data-ng-bind="player.player"></td>
                        <td data-ng-bind="score(player)"></td>
                    </tr>
                </table>
            </div>
        </div>
     </div>
     <div id="footer">Copyright &copy; 2015 Case Western Reserve University</div>
 </body>
</html>
