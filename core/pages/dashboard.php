<!doctype html>
<html data-ng-app="cwruSelfAssessment">
 <head>
  <title>Self Assessment Game</title>
  <meta charset="utf-8">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/game.css">
  <script type="text/javascript" src="js/angular.min.js"></script>
  <script type="text/javascript" src="js/db-controllers.js"></script>
 </head>
 <body data-ng-controller="CwruSelfAssessmentDashboard">
     <h1>Self Assessment Game</h1>
     <div id="msg">
         Logged in as <span data-ng-bind="model.user"></span>.
     </div>
     <div id="admin" data-ng-if="isInstructor()">
         <a href="index.php?route=admin">Administration</a>
     </div>
     <div data-ng-if="landing()">
        <button class="btn-lg btn-primary" data-ng-click="gtChooseCourse()">Create A Game</button>
        <hr />
        <div id="db-right-col">
            <h2>Created</h2>
            <ul data-ng-if="!objEmpty(model.games.created)">
                <li data-ng-repeat="game in model.games.created">
                    <span data-ng-bind="game.course"></span>
                    <a class="btn btn-default btn-primary" data-ng-href="{{'index.php?route=game&amp;game='+game.game}}">Play</a>
                    <button class="btn btn-default btn-warning" data-ng-click="endGame(game)">End</button>
                </li>
            </ul>
            <span data-ng-if="objEmpty(model.games.created)">None yet...</span>
        </div>
        <div id="db-left-col">
            <h2>Invited</h2>
            <ul data-ng-if="!objEmpty(model.games.invited)">
                <li data-ng-repeat="game in model.games.invited">
                    <span data-ng-bind="game.course"></span>
                    <a class="btn btn-default btn-primary" data-ng-href="{{'index.php?route=game&amp;game='+game.game}}">Play</a>
                    <button class="btn btn-default btn-warning" data-ng-click="declineGame(game)">Decline</button>
                    by <span data-ng-bind="game.user"></span>
                </li>
            </ul>
            <span data-ng-if="objEmpty(model.games.invited)">None yet...</span>
        </div>
     </div>
     <div data-ng-if="chooseCourse()">
         <label>Courses</label>
         <select data-ng-model="model.createGameCourse" data-ng-options="course for course in model.courses">
             <option value="">---Select A Course---</option>
         </select>
         <hr />
         <button class="btn btn-default btn-primary" data-ng-click="gtChooseQuestionSet()">Continue</button>
         <button class="btn btn-default btn-warning" data-ng-click="cancelCreateGame()">Cancel</button>
     </div>
     <div data-ng-if="chooseQuestionSet()">
         <label>Question Sets</label>
         <select data-ng-model="model.createGameQuestionSet" data-ng-options="set.title for set in model.questionSets">
             <option value="">---Select A Set---</option>
         </select>
         <hr />
         <button class="btn btn-default btn-primary" data-ng-click="gtInvitePlayers()">Continue</button>
         <button class="btn btn-default btn-warning" data-ng-click="cancelCreateGame()">Cancel</button>
     </div>
     <div data-ng-if="invitePlayers()">
         <div class="left-block">
            <label>Players</label>
            <ol>
                <li data-ng-if="model.createGamePlayers" data-ng-repeat="player in model.createGamePlayers">
                    <span class="player-name" data-ng-bind="player"></span>
                    <button class="btn btn-default btn-warning" data-ng-click="deletePlayer(player)">Remove</button>
                </li>
                <li data-ng-if="!model.createGamePlayers">
                    None
                </li>
            </ol>
            <button class="btn btn-default btn-primary" data-ng-click="addPlayer()">Add</button>
            <input type="text" data-ng-model="model.createGamePlayer" value="" />
         </div>
         <hr />
         <button class="btn btn-default btn-primary" data-ng-click="gtConfirmGame()">Continue</button>
         <button class="btn btn-default btn-warning" data-ng-click="cancelCreateGame()">Cancel</button>
     </div>
     <div data-ng-if="confirmGame()">
         <div class="left-block">
            Course: <span data-ng-bind="model.createGameCourse"></span><br/>
            Questions: <span data-ng-bind="model.createGameQuestionSet.title"></span><br/>
            Players: <span data-ng-bind="model.createGamePlayers"></span><br/>
         </div>
         <hr />
         <button class="btn btn-default btn-primary" data-ng-click="createGame()">Create Game?</button>
         <button class="btn btn-default btn-warning" data-ng-click="cancelCreateGame()">Cancel</button>
     </div>
     <div id="footer">Copyright &copy; 2015 Case Western Reserve University</div>
 </body>
</html>
