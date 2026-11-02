<html>
    <head>
        <?php
session_start();
header("Cache-Control: no-store");   // stops the Back button showing this page after logout

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}
?>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Green Paper Admin</title>
        <link href="https://fonts.googleapis.com/css2?family=Lora&display=swap" rel="stylesheet">
        <style>
            :root{
                --green:#1f6b3e;
                --bg:#f4f1ea;
                --card:#fff;
                --line:#ddd8cc;
                --red:#a32a2a;
            }
            *{
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            
            }
            body{
                background: var(--bg);
                font-family: Arial;

            }
            h1,h2,h3{
                font-family: 'Lora',serif;
                font-weight: 400;
            }
            .topbar{
                background: var(--green);
                color: #fff;
                display: flex;
                align-items: center;
                gap: 24px;
                padding: 0 40px;
                height: 56px;
            }
            .topbar nav{
                display: flex;
                gap: 22px ;
            }
            .topbar a{
                color: #fff;
                text-decoration: none;
                font-size: 20px;
            }
            .topbar a.active{
                border-bottom: 2px solid #fff;
                padding-bottom: 4px;
            }
            .user{
                margin-left: auto;
            }
            .container{
                max-width: 1152px;
                margin: 0 auto;
                padding: 40px 24px;
            }
            .eyebrow{
                color: var(--green);
                font-size: 13px;
                font-weight: bold;
                letter-spacing: 1px;
            }
            .eyebrow-row{
                display: flex;
                justify-content: space-between;
            }
            h1{
                font-size: 42px;
                margin: 16px 0 22px;
            }
            .stats{
                display: grid;
                grid-template-columns: repeat(4,1fr);
                gap: 18px;
                margin-bottom: 30px;
            }
            .stat{
                background: var(--card);
                border: 1px solid var(--line);
                padding: 20px;
            }
            .num{
                font-family: 'Lora',serif;
                font-size: 44px;
            }
            .label{
                font-size: 11px;
                letter-spacing: 1px;
                color: #555;
                font-weight: bold;
            }
            .card-head h2{
                font-size: 22px;
            }
            .card-head a{
                color: var(--green);
                font-weight: bold;
                font-size: 14px;
                text-decoration: none;
            }
            .layout{
                display: grid;
                grid-template-columns: 1.6fr 1fr;
                gap: 24px;
                align-items: start;
            }
            .col-left,.col-right{
                display: flex;
                flex-direction: column;
                gap: 24px;
            }
            .card{
                background: var(--card);
                border: 1px solid var(--line);


            }
            .card-head{
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px 22px;
                border-bottom: 1px solid var(--line);
            }
            .row{
                border-bottom: 1px solid var(--line);
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 18px 22px;
                
            }
            .btn{
                cursor: pointer;
                border: 1px solid var(--green);
                color: var(--green);
                background: #fff;
                padding: 10px 18px;
                font-weight: bold;

            }
            .bar-row{
                display: grid;
                grid-template-columns:100px 1fr 40px;
                gap: 12px;
                padding: 12px 20px;
            }
            .track{
                background: #eee9e0;
                height: 18px;

            }
            .fill{
                background: var(--green);
                height: 100%;
            }
            .read{
                list-style: none;

            }
            .read li{
                display: grid;
                grid-template-columns: 36px 1fr auto;
                padding: 10px 22px;
            }
            .rank{
                color: var(--green);
                font-size: 22px;
            }
            .action{
                list-style: none;
            }
            .action a{
                display: flex;
                justify-content: space-between;
                padding: 14px 22px;
                border-top: 1px solid var(--line);
                text-decoration: none;
                color: #222;
            }
            .num.warn{
                color: var(--red);
            }
            .stat a{
                color: var(--green);
                font-weight: bold;
                text-decoration: none;
            }
            .btn:hover{
                background: var(--green);
                color: #fff;
            }
            @media(max-width:900px){
                .stats{
                    grid-template-columns: repeat(2,1fr);
                }
                .layout{
                    grid-template-columns: 1fr;

                }
            }

        </style>

    </head>
    <body>
        <header class="topbar">
            <h2 class="logo">Green Paper</h2>
            <nav>
                <a class="active" href="#">Overview</a>
                <a  href="#">Pending</a>
                <a  href="#">Articles</a>
                <a  href="#">Categories</a>
                <a  href="#">Announcements</a>
                <a  href="#">Comments</a>
                <a  href="#">Users</a>
                <a  href="#">Reports</a>
            </nav>
            <div class="user">Hi Admin <a href="#">Log Out</a></div>
        </header>
        <main class="container">
            <div class="eyebrow-row">
                <span class="eyebrow">OVERVIEW</span>
                <span class="date">Tuesday, 13 October 2026</span>
            </div>
            <h1>Today at Green Paper</h1>

            <section class="stats">
                <div class="stat">
                    <div class="num">14</div>
                    <div class="label">PUBLISHED ARTICLES</div>
                    <p>Live on the site</p>
                </div>
                <div class="stat">
                    <div class="num warn">2</div>
                    <div class="label">WAITING FOR APPROVAL</div>
                    <p>Needs an admin</p>
                </div>
                <div class="stat">
                    <div class="num">128</div>
                    <div class="label">TOTAL VIEWS</div>
                    <p>9 per published article</p>
                </div>
                <div class="stat">
                    <div class="num">5</div>
                    <div class="lable">REGISTERED USERS</div>
                    <p><a href="#">Manage users</a></p>
                </div>

                

            </section>
            <div class="layout">
                <div class="col-left">
                    <section class="card">
                        <div class="card-head"><h2>Waiting for approval</h2><a href="#">Review all</a></div>
                        
                       
                        <div class="row">
                            <div><h3>Football Trials for the University Team</h3>
                                <small>By A. Perera • Sports 07 Oct 2026</small>
                            </div>
                            <button class="btn">Review</button>

                        </div>
                        <div class="row">
                            <div><h3>Career Fair Returns With 40 Employers</h3>
                                <small>By S. Fernando • Events • 06 Oct 2026</small>
                            </div>
                            <button class="btn">Review</button>

                        </div>
                    </section>

                    <section class="card">
                        <div class="card-head"><h2>Views by category</h2></div>
                        <div class="bar-row">
                            <span>Academic</span>
                            <div class="track"><div class="fill" style="width: 100%;"></div></div>
                            <b>49</b>
                        </div>
                        <div class="bar-row">
                            <span>Events</span>
                            <div class="track"><div class="fill" style="width: 78%;"></div></div>
                            <b>38</b>
                        </div>
                        <div class="bar-row">
                            <span>General</span>
                            <div class="track"><div class="fill" style="width: 49%;"></div></div>
                            <b>28</b>
                        </div>
                        <div class="bar-row">
                            <span>Sports</span>
                            <div class="track"><div class="fill" style="width: 35%;"></div></div>
                            <b>17</b>
                        </div>
                    </section>
                    
                </div>


                <div class="col-right"><section class="card">
                        <div class="card-head"><h2>Most read</h2></div>
                        <ol class="read">
                            <li><span class="rank">1</span><span>NSBM Students Win the National Robotics...</span><em>38</em></li>
                            <li><span class="rank">2</span><span>Semester 2 Exam Timetable Published</span><em>29</em></li>
                            <li><span class="rank">3</span><span>New Scholarship Applications Now Open</span><em>20</em></li>
                            <li><span class="rank">4</span><span>Inter-Faculty Cricket Final This Friday</span><em>17</em></li>
                            <li><span class="rank">5</span><span>Graduation Rehearsal on 14 October</span><em>12</em></li>

                        </ol>
                    </section>
                    <section class="card">
                        <div class="card-head"><h2>Quick actions</h2></div>
                        <ul class="action">
                            <li><a href="#">Write a new article<span>→</span></a></li>
                            <li><a href="#">Manage categories<span>→</span></a></li>
                            <li><a href="#">Announcements<span>→</span></a></li>
                            <li><a href="#">Readership reports<span>→</span></a></li>


                        </ul>
                    </section></div>

            </div>
        

        </main>
            <div>
        <h1>Welcome,<span><?= htmlspecialchars($_SESSION['name']) ?></span> </h1>
        <p>This is an <span>admin</span> page.</p>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
        
    </body>
</html>