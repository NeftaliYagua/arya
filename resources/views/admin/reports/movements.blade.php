<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 
<title></title>
<style>
  table, td, th {
    border: 1px solid black;
    font-size: x-small;
  }
  
  table {
    border-collapse: collapse;
    width: 100%;
  }
  
 /* th {
    
    text-align: left;
  } */
  </style>
</head>

<body>
  <br>
  <h4 style="color: black; text-align: center">HISTORIAL INVENTARIO</h4>
 <?php 
    
    $total_por_facturar = 0;
    $total_por_cobrar = 0;
  
  ?>
<table style="width: 100%;">
  <tr>
    <th style="text-align: center; width:9%;">Fecha</th>
    <th style="text-align: center; width:5%;">ID Prod</th>
    <th style="text-align: center; width:7%;">Codigo</th>
    <th style="text-align: center; width:9%;">Descripción</th>
    <th style="text-align: center; width:5%;">Tipo</th>
    <th style="text-align: center; width:7%;">Precio</th>
    <th style="text-align: center; width:5%;">Cantidad</th>
    <th style="text-align: center; width:5%;">Cant. Actual</th>
    <th style="text-align: center; width:5%;">Num.Fac</th>
    <th style="text-align: center; width:5%;">Num.Nota</th>
    <th style="text-align: center; width:5%;">Sucursal</th>
  </tr>
    @foreach ($inventories as $inventory)
        <tr>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->date}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->id_product}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->code_comercial}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->description}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->type}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->price}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->amount}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->amount_real}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->number_invoice}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->number_invoice}}</td>
          <td style="text-align: center; font-weight: normal;">{{ $inventory->branch}}</td>
        </tr>
      @endforeach
  </tbody>
</table>

</body>
</html>