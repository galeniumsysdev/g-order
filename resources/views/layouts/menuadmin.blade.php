<ul class="nav navbar-nav side-nav">
    <li>
        <a href="{{route('admin.index')}}"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
    </li>
    <li>
      <a href="javascript:;" data-toggle="collapse" data-target="#user"><i class="fa fa-fw fa-user"></i> User <i class="fa fa-fw fa-caret-down"></i></a>
      @if($menu=='customer-oracle' or $menu=='user' or $menu=='custYasa' )
      <ul id="user" class="collapse in">
      @else
      <ul id="user" class="collapse">
      @endif
        <li {{$menu=='customer-oracle'?'class=active':''}}>
            <a href="{{route('useroracle.index')}}"><i class="fa fa-fw fa-user"></i> Customer Oracle</a>
        </li>
        <li {{$menu=='custYasa'?'class=active':''}}>
            <a href="{{route('customer.yasaNonOracle')}}"><i class="fa fa-fw fa-user"></i> Customer Yasa Non Oracle</a>
        </li>
        <li {{$menu=='user'?'class=active':''}}>
            <a href="{{route('users.index')}}"><i class="fa fa-fw fa-user"></i> Other User</a>
        </li>
      </ul>
    </li>
    <li>
      <a href="javascript:;" data-toggle="collapse" data-target="#product"><i class="fa fa-fw fa-table"></i> Product <i class="fa fa-fw fa-caret-down"></i></a>
      @if($menu=='product' or $menu=='listprice' or $menu=='diskon' )
      <ul id="product" class="collapse in">
      @else
      <ul id="product" class="collapse">
      @endif
        <li {{$menu=='product'?'class=active':''}}>
            <a href="{{route('product.show')}}"><i class="fa fa-fw fa-table"></i> Product</a>
        </li>
        <li {{$menu=='listprice'?'class=active':''}}>
            <a href="{{route('product.priceindex')}}"><i class="fa fa-fw fa-table"></i> Pricing</a>
        </li>
        <li {{$menu=='diskon'?'class=active':''}}>
            <a href="{{route('product.searchDiskon')}}"><i class="fa fa-fw fa-table"></i> Diskon</a>
        </li>
        <li {{$menu=='pareto'?'class=active':''}}>
            <a href="{{route('product.pareto')}}"><i class="fa fa-fw fa-star"></i> Pareto Product</a>
        </li>
      </ul>
    </li>

    <li {{$menu=='banner'?'class=active':''}}>
        <a href="{{route('admin.banner')}}"><i class="fa fa-fw fa-film"></i> Banner/Carousel</a>
    </li>

    <li {{$menu=='role'?'class=active':''}}>
        <a href="{{route('role.index')}}"><i class="fa fa-fw fa-edit"></i> Role</a>
    </li>
    <li {{$menu=='permission'?'class=active':''}}>
        <a href="{{route('permission.index')}}"><i class="fa fa-fw fa-wrench"></i> Permission</a>
    </li>
    <li {{$menu=='OrgStructure'?'class=active':''}}>
        <a href="{{route('org.list')}}"><i class="fa fa-fw fa-sitemap"></i> Org Strucure</a>
    </li>
    <li>
        <a href="javascript:;" data-toggle="collapse" data-target="#category"><i class="fa fa-fw fa-arrows-v"></i> Category <i class="fa fa-fw fa-caret-down"></i></a>
        @if(in_array($menu,array("CategoryOutlet","CategoryProduct","GroupDatacenter","SubgroupDatacenter")) )
        <ul id="category" class="collapse in">
        @else
        <ul id="category" class="collapse">
        @endif
            <li {{$menu=='CategoryOutlet'?'class=active':''}}>
                <a href="{{route('CategoryOutlet.index')}}"><i class="fa fa-fw fa-desktop"></i> Categories Outlet</a>
            </li>
            <li {{$menu=='CategoryProduct'?'class=active':''}}>
                <a href="{{route('CategoryProduct.index')}}"><i class="fa fa-fw fa-desktop"></i> Categories Product</a>
            </li>
            <li {{$menu=='GroupDatacenter'?'class=active':''}}>
                <a href="{{route('GroupDataCenter.index')}}"><i class="fa fa-fw fa-desktop"></i> Group Datacenter</a>
            </li>
            <li {{$menu=='SubgroupDatacenter'?'class=active':''}}>
                <a href="{{route('SubgroupDatacenter.index')}}"><i class="fa fa-fw fa-desktop"></i> Subgroup Datacenter</a>
            </li>
            <li {{$menu=='flexvalue'?'class=active':''}}>
                <a href="{{route('flexvalue.index')}}"><i class="fa fa-fw fa-desktop"></i> Cth Product</a>
            </li>
        </ul>
    </li>
</ul>
