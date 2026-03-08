<?php
/**
 * Size Guide Page
 */

require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Size Guide';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .size-guide-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .size-guide-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .size-guide-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .size-guide-header p {
            font-size: 1.125rem;
            color: #666;
        }
        
        .size-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .size-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .size-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }
        
        .size-table th,
        .size-table td {
            padding: 1rem;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        
        .size-table th {
            background: #1a1a1a;
            color: white;
            font-weight: 600;
        }
        
        .size-table tr:nth-child(even) {
            background: #f8f8f8;
        }
        
        .measure-tip {
            background: #f0f7ff;
            border-left: 4px solid #2196F3;
            padding: 1.25rem;
            margin: 1.5rem 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="container" style="padding-top: 90px; padding-bottom: 3rem;">
        <nav class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
            <span class="separator">/</span>
            <span class="current">Size Guide</span>
        </nav>
        
        <div class="size-guide-container">
            <div class="size-guide-header">
                <h1>Size Guide</h1>
                <p>Find your perfect fit with our comprehensive sizing charts</p>
            </div>
            
            <!-- How to Measure -->
            <div class="size-section">
                <h2>How to Measure</h2>
                <div class="measure-tip">
                    <strong>Tip:</strong> For best results, have someone help you measure. Use a soft measuring tape 
                    and wear fitted clothing or undergarments.
                </div>
                
                <h3>Body Measurements:</h3>
                <ul>
                    <li><strong>Chest/Bust:</strong> Measure around the fullest part of your chest</li>
                    <li><strong>Waist:</strong> Measure around your natural waistline</li>
                    <li><strong>Hips:</strong> Measure around the fullest part of your hips</li>
                    <li><strong>Inseam:</strong> Measure from crotch to ankle</li>
                    <li><strong>Shoulder:</strong> Measure from shoulder point to shoulder point across back</li>
                </ul>
            </div>
            
            <!-- Women's Clothing -->
            <div class="size-section">
                <h2>Women's Clothing</h2>
                <table class="size-table">
                    <thead>
                        <tr>
                            <th>Size</th>
                            <th>Bust (inches)</th>
                            <th>Waist (inches)</th>
                            <th>Hips (inches)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>XS</strong></td>
                            <td>31-32</td>
                            <td>24-25</td>
                            <td>34-35</td>
                        </tr>
                        <tr>
                            <td><strong>S</strong></td>
                            <td>33-34</td>
                            <td>26-27</td>
                            <td>36-37</td>
                        </tr>
                        <tr>
                            <td><strong>M</strong></td>
                            <td>35-36</td>
                            <td>28-29</td>
                            <td>38-39</td>
                        </tr>
                        <tr>
                            <td><strong>L</strong></td>
                            <td>37-39</td>
                            <td>30-32</td>
                            <td>40-42</td>
                        </tr>
                        <tr>
                            <td><strong>XL</strong></td>
                            <td>40-42</td>
                            <td>33-35</td>
                            <td>43-45</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Men's Clothing -->
            <div class="size-section">
                <h2>Men's Clothing</h2>
                <table class="size-table">
                    <thead>
                        <tr>
                            <th>Size</th>
                            <th>Chest (inches)</th>
                            <th>Waist (inches)</th>
                            <th>Hips (inches)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>S</strong></td>
                            <td>34-36</td>
                            <td>28-30</td>
                            <td>35-37</td>
                        </tr>
                        <tr>
                            <td><strong>M</strong></td>
                            <td>38-40</td>
                            <td>32-34</td>
                            <td>38-40</td>
                        </tr>
                        <tr>
                            <td><strong>L</strong></td>
                            <td>42-44</td>
                            <td>36-38</td>
                            <td>41-43</td>
                        </tr>
                        <tr>
                            <td><strong>XL</strong></td>
                            <td>46-48</td>
                            <td>40-42</td>
                            <td>44-46</td>
                        </tr>
                        <tr>
                            <td><strong>XXL</strong></td>
                            <td>50-52</td>
                            <td>44-46</td>
                            <td>47-49</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Shoes -->
            <div class="size-section">
                <h2>Shoe Sizes</h2>
                <table class="size-table">
                    <thead>
                        <tr>
                            <th>US Women</th>
                            <th>US Men</th>
                            <th>EU</th>
                            <th>UK</th>
                            <th>CM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>5</td>
                            <td>3.5</td>
                            <td>35-36</td>
                            <td>2.5</td>
                            <td>22</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>4.5</td>
                            <td>36-37</td>
                            <td>3.5</td>
                            <td>23</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>5.5</td>
                            <td>37-38</td>
                            <td>4.5</td>
                            <td>24</td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>6.5</td>
                            <td>38-39</td>
                            <td>5.5</td>
                            <td>25</td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>7.5</td>
                            <td>39-40</td>
                            <td>6.5</td>
                            <td>26</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>8.5</td>
                            <td>40-41</td>
                            <td>7.5</td>
                            <td>27</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Tips -->
            <div class="size-section">
                <h2>Sizing Tips</h2>
                <ul>
                    <li>Check individual product measurements in the description</li>
                    <li>Vintage and thrifted items may fit differently than modern sizing</li>
                    <li>When in doubt, size up for a more comfortable fit</li>
                    <li>Contact us if you need help determining your size</li>
                    <li>Remember: Each thrifted item is unique and may vary slightly</li>
                </ul>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
