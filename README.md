# Food Nutrition Analyzer

A comprehensive, responsive web application for analyzing and comparing food nutrition information with professional-grade features and modern UI design. Built with HTML, CSS, JavaScript, and enhanced with advanced functionality for nutrition education and health awareness.

## Table of Contents

- [Features Overview](#features-overview)
  - [Core Functionality](#core-functionality)
  - [Advanced Features](#advanced-features)
  - [Data Visualization](#data-visualization)
  - [Modern UI/UX Design](#modern-uiux-design)
- [How to Use](#how-to-use)
  - [Setup and Launch](#1-setup-and-launch)
  - [Browse and Search](#2-browse-and-search)
  - [Single Food Analysis](#3-single-food-analysis)
  - [Multiple Food Comparison](#4-multiple-food-comparison)
  - [Team Information](#5-team-information)
  - [Understanding the Results](#6-understanding-the-results)
- [Technical Specifications](#technical-specifications)
  - [Technologies Used](#technologies-used)
  - [Browser Compatibility](#browser-compatibility)
  - [Data Sources](#data-sources)
- [File Structure](#file-structure)
- [Key Features Explained](#key-features-explained)
- [Development Team](#development-team)
- [License and Attribution](#license-and-attribution)

## Features Overview

### Core Functionality
- **Advanced Search System**: Real-time search through 1000+ food items with Indonesian name preservation
- **Smart Pagination**: Navigate through data with 10 items per page for optimal performance
- **Intelligent Sorting**: Click column headers to sort by energy, protein, fat, carbohydrates with proper text/numeric handling
- **Detailed Nutrition Analysis**: Comprehensive nutrient breakdown with health standard comparisons
- **Standard Comparison**: Compare against recommended daily values for children (1-5 years)
- **Dynamic Clear Button**: Clear search functionality that appears only when needed

### Advanced Features
- **Multiple Food Comparison**: Select and compare up to multiple foods simultaneously with interactive charts
- **Professional Charts**: Interactive Chart.js visualizations with customizable nutrient selection
- **Responsive Tables**: Mobile-optimized tables with horizontal scrolling and touch-friendly interface
- **Smart Food Selection**: Checkbox-based selection system with bulk operations
- **Comparison Toolbar**: Dedicated toolbar for managing multiple selections
- **Nutritional Insights**: AI-powered recommendations and analysis for selected foods

### Data Visualization
- **Interactive Bar Charts**: Visual comparison of food values vs recommended ranges
- **Multi-Food Charts**: Compare multiple foods across different nutrients simultaneously  
- **Nutrition Summary Cards**: Quick overview of total nutrients analyzed and safety percentages
- **Color-Coded Status**: Visual indicators for normal range (green), excess (red), deficiency (blue)
- **Chart Controls**: Switch between different nutrients for focused comparisons

### Modern UI/UX Design
- **Compact Header Design**: Space-efficient horizontal layout with centered apple logo
- **Mobile-First Responsive**: Adaptive design that works flawlessly on all devices
- **Progressive Enhancement**: Enhanced features for larger screens, optimized basics for mobile
- **Smooth Animations**: Subtle hover effects and transitions for premium user experience
- **Professional Typography**: Carefully selected fonts and spacing for optimal readability
- **Sticky Footer**: Always-visible attribution and technical information

## How to Use

### 1. Setup and Launch
```bash
# Option 1: Open directly in browser
# Simply double-click index.html or open it in any modern web browser

# Option 2: Use local server (recommended for full functionality)
cd "UTS_Data Nutrition Food"
python3 -m http.server 8000
# Then open http://localhost:8000 in your browser

# Option 3: Use Node.js server
npx http-server -p 8000
# Then open http://localhost:8000 in your browser
```

### 2. Browse and Search
- **Search Foods**: Use the search bar to find specific foods by name (supports Indonesian terms)
- **Smart Pagination**: Navigate through pages using the pagination controls at the bottom
- **Sort Data**: Click column headers to sort by different nutrition values (A-Z for names, numeric for values)
- **Clear Filters**: Clear button appears automatically when searching to reset and show all foods

### 3. Single Food Analysis
- **Select Food**: Click the "Analyze" button next to any food item
- **View Summary**: Check the summary cards showing total nutrients and safety percentage
- **Review Chart**: Interactive bar chart compares food values against standards
- **Study Details**: Detailed table shows exact values, ranges, and status for each nutrient
- **Learn More**: Click info buttons (ℹ️) to see nutrient functions and health effects

### 4. Multiple Food Comparison
- **Select Multiple Foods**: Use checkboxes to select multiple foods for comparison
- **Comparison Toolbar**: Appears when foods are selected with "Compare Selected" and "Clear Selection" options
- **Interactive Charts**: Choose specific nutrients to compare across selected foods
- **Detailed Comparison Table**: Side-by-side nutrition comparison with all selected foods
- **Nutritional Insights**: Get recommendations and insights based on the food combination

### 5. Team Information
- **About Team Page**: Click "About Team" to learn about the developers
- **Project Statistics**: View comprehensive project data and technology stack
- **Development Team**: Meet the contributors including AI assistance acknowledgment

### 6. Understanding the Results

#### Status Colors
- 🟢 **Green (Normal Range)**: Nutrient level is within recommended range
- 🔴 **Red (Excess)**: Nutrient level exceeds recommended maximum
- 🔵 **Blue (Deficient)**: Nutrient level below recommended minimum
- ⚪ **Gray (No Standard)**: No established recommendation available

#### Summary Information
- **Total Nutrients Analyzed**: Number of nutrients with available data
- **Nutrients in Safe Range**: Count of nutrients within recommended levels
- **Safety Percentage**: Percentage of nutrients in safe range

## Technical Specifications

### Technologies Used
- **HTML5**: Semantic structure with accessibility features and proper document architecture
- **CSS3**: Advanced styling with CSS Grid, Flexbox, custom properties, and responsive design patterns
- **JavaScript ES6+**: Modern interactive functionality, data processing, and state management
- **jQuery 3.7.1**: Efficient DOM manipulation, event handling, and AJAX requests
- **Chart.js 3.9.1**: Professional interactive data visualization with customizable charts
- **Font Awesome 6.4.0**: Comprehensive icon library for enhanced UI indicators
- **CSV Processing**: Custom CSV parsing with support for Indonesian food names and special characters

### Data Sources
- **foods.csv**: Comprehensive nutrition data for 1000+ food items
- **standard-nutrition.csv**: Recommended daily values and health information
- Source: Kaggle Food Nutrition Dataset

## File Structure
```
nutrition-food-analyzer/
├── index.html              # Main application with search, tables, modals, and navigation
├── about.html              # Team information page with contributor details
├── style.css               # Comprehensive responsive styling and modern design system
├── script.js               # Core functionality: search, pagination, comparison, charts
├── foods.csv               # Food nutrition database (1000+ items)
├── standard-nutrition.csv  # Health standards and recommendations for children
└── README.md               # Complete project documentation
```

## Key Features Explained

### 1. CSV Data Loading
- Automatic loading of both CSV files using fetch API
- Robust CSV parsing handling quotes and special characters
- Error handling for missing or corrupt data files

### 2. Search and Filter System
- Real-time search with instant results
- Case-insensitive partial matching
- Results counter showing filtered items

### 3. Intelligent Table Sorting
- Click any column header to sort data with proper type handling
- Food names sorted alphabetically using localeCompare for proper Indonesian name ordering
- Numeric columns sorted mathematically with proper handling of decimal values
- Visual indicators showing current sort direction with smooth transitions
- Maintains sort state during pagination navigation

### 4. Advanced Pagination System
- Efficient 10-items-per-page display for optimal performance
- Smart page controls with numbered navigation
- "Previous" and "Next" buttons with proper state management  
- Page info showing current position and total items
- Maintains search and sort state across page changes
- Responsive pagination controls for mobile devices

### 5. Multi-Food Comparison System
- Checkbox-based selection system for multiple foods
- Dynamic comparison toolbar that appears when foods are selected
- Interactive charts with nutrient selection dropdown
- Side-by-side detailed comparison tables
- Nutritional insights and recommendations engine
- Bulk selection management with clear all functionality

### 6. Professional Modal System
- Single food analysis modal with comprehensive data
- Multi-food comparison modal with advanced charts
- Responsive design optimized for all screen sizes
- Smooth animations and transitions
- Keyboard accessibility and proper focus management

### 7. Mobile-First Responsive Design
- Progressive enhancement from mobile to desktop
- Horizontal header layout optimized for space efficiency
- Apple logo hidden on mobile for cleaner appearance
- Touch-friendly interface with appropriate tap targets
- Flexible grid layouts that adapt seamlessly
- Print-optimized styles for documentation and reports

## Development Team

This project was developed as part of a Web Programming (PemWeb) course assignment with collaborative effort:

### Human Contributors
- **Muhammad Brian Abdillah**
- **Renita Dwi Setiyani**
- **Hanson Philip**

### AI Development Assistance
- **Claude Sonnet 4**

## License and Attribution

### Open Source Components
- **Data Source**: [Kaggle - Data Makanan Stunting by Nauval Almas](https://www.kaggle.com/datasets/nauvalalmas/data-makanan-stunting)
- **Icons**: Font Awesome 6.4.0 (Free License)
- **Charts**: Chart.js 3.9.1 (MIT License)  
- **jQuery**: jQuery 3.7.1 (MIT License)

### Technical Approach
- **Framework**: Pure HTML/CSS/JavaScript (No heavy framework dependencies)
- **Architecture**: Modern ES6+ JavaScript with progressive enhancement
- **Design**: Mobile-first responsive design with accessibility considerations
- **Performance**: Optimized for fast loading and smooth interactions

---