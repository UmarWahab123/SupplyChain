<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
          <tr>                
              <th style="background: yellow;"><b>Category</b></th>
              <th style="background: yellow;"><b>Sub Category</b></th>
              <th><b>Hs Code</b></th>                    
              <th><b>Tax by book %</b></th>
              <th><b>Vat %</b></th>
              <th><b>Prefix</b></th>
              <?php foreach($customerCategory as $customerCat){ ?>
                <th style="background: yellow;"><b><?php echo $customerCat->title ?> Default Markup Value %</b></th>
              <?php } ?>
          </tr>
        </thead>
        <tbody>
          
        </tbody> 
    
    </table>

    </body>
</html>
