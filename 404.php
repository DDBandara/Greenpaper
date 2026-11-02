<?php
http_response_code(404);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - 404 | Green Paper</title>
    
    <link href="https://googleapis.com" rel="stylesheet">
    
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Poppins', -apple-system, sans-serif; 
        }
        
        body { 
            background-color: rgb(247, 249, 246); 
            color: rgb(43, 43, 43); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            padding: 20px; 
        }
        
        .error-container { 
            max-width: 1100px; 
            width: 100%; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            gap: 60px; 
        }

        .text-section { 
            flex: 1; 
            max-width: 550px; 
            padding-left: 40px;
        }

        .error-tag { 
            font-size: 13px; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            color: rgb(101, 109, 74); 
            margin-bottom: 12px; 
            font-weight: 600; 
        }

        h1 { 
            font-size: 4.5rem; 
            font-weight: 700; 
            color: rgb(19, 42, 19); 
            line-height: 1.1; 
            margin-bottom: 15px; 
        }

        p { 
            font-size: 1.25rem; 
            color: rgb(79, 93, 47); 
            margin-bottom: 40px; 
            line-height: 1.6;
        }

        .help-text { 
            font-size: 14px; 
            color: rgb(101, 109, 74); 
            margin-bottom: 15px; 
        }

        .btn-group { 
            display: flex; 
            gap: 15px; 
            flex-wrap: wrap; 
        }
        
        .btn { 
            text-decoration: none; 
            color: rgb(255, 255, 255); 
            background-color: rgb(17, 101, 48); 
            padding: 12px 32px; 
            border-radius: 4px; 
            font-size: 14px; 
            font-weight: 600; 
            transition: all 0.2s ease; 
            display: inline-block;
        }

        .btn:hover { 
            background-color: rgb(11, 79, 35); 
            transform: translateY(-1px);
        }

        .btn-outline { 
            background-color: rgb(255, 255, 255); 
            color: rgb(43, 43, 43); 
            border: 1px solid rgb(149, 165, 166); 
        }

        .btn-outline:hover { 
            background-color: rgb(241, 242, 246); 
            transform: translateY(-1px);
        }
        
        .visual-section { 
            flex: 1.5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
        }

        .error-image {
            max-width: 100%;
            width: 800px; 
            height: auto;
            display: block;
        }
        
        @media (max-width: 768px) {
            .error-container { 
                flex-direction: column-reverse; 
                text-align: center; 
            }
            .text-section { 
                max-width: 100%; 
            }
            h1 { 
                font-size: 3rem; 
            }
            .btn-group { 
                justify-content: center;
             }
            .error-image { 
                max-height: 300px; 
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="error-container">
        <div class="text-section">
            <div class="error-tag">Error Code 404</div>
            <h1>OOOPS!!</h1>
            <p>This is not the page you are looking for</p>
            
            <div class="help-text">Here are some helpful links instead:</div>
            <div class="btn-group">
                <a href="index.php" class="btn">Return Home</a>
                <a href="#" class="btn btn-outline">Save draft</a>
            </div>
        </div>

        <div class="visual-section">
            <img src="image5.jpg" alt="404 Error Graphic" class="error-image">
        </div>
    </div>

</body>
</html>




























