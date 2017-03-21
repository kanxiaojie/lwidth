@extends('layout')

@section('content')
    <div class="row clearfix">
        <div class="col-md-4 column">
        </div>
        <div class="col-md-4 column">
            <form name="form" role="form" method="POST" action="{{url('/images/upload')}}" enctype="multipart/form-data" onsubmit="return isValidateFile('file');">

                <div class="form-group">
                    <label for="exampleInputFile">选择文件</label><input type="file" name='file' />
                    <p class="help-block">

                    </p>
                </div>
                <button type="submit" class="btn btn-default">上传</button>
            </form>
        </div>

    </div>

    <script>
        function isValidateFile(obj) {
            var extend = document.form.file.value.substring(document.form.file.value.lastIndexOf(".") + 1);
            if (extend == "") {
                alert("请选择图片！");
                return false;
            }
            else {
                if (!(extend == "jpg" || extend == "png" || extend =="gif")) {
                    alert("请上传后缀名为jpg、png或gif的文件!");
                    return false;
                }
            }
            return true;
        }
    </script>
@endsection