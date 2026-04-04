package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.OrderDto;
import com.sieuthithucung.entity.OrderEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.OrderMapper;
import com.sieuthithucung.repository.OrderRepository;
import com.sieuthithucung.service.OrderService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class OrderServiceImpl implements OrderService {

    private final OrderRepository repository;

    public OrderServiceImpl(OrderRepository repository) {
        this.repository = repository;
    }
    @Override
    public OrderDto create(OrderDto dto) {
        return createInternal(dto);
    }

    @Override
    public OrderDto update(Long id, OrderDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Order not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public OrderDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<OrderDto> getAll() {
        return getAllInternal();
    }

    private OrderDto createInternal(OrderDto dto) {
        OrderEntity entity = OrderMapper.mapToOrderEntity(dto);
        OrderEntity saved = repository.save(entity);
        return OrderMapper.mapToOrderDto(saved);
    }

    private OrderDto updateInternal(Long id, OrderDto dto) {
        OrderEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Order not found with id: " + id));

        OrderEntity source = OrderMapper.mapToOrderEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        OrderEntity saved = repository.save(existing);
        return OrderMapper.mapToOrderDto(saved);
    }

    private OrderDto findByIdInternal(Long id) {
        OrderEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Order not found with id: " + id));
        return OrderMapper.mapToOrderDto(entity);
    }

    private List<OrderDto> getAllInternal() {
        return repository.findAll().stream().map(OrderMapper::mapToOrderDto).toList();
    }

    private String[] getNullPropertyNames(Object source) {
        BeanWrapper src = new BeanWrapperImpl(source);
        PropertyDescriptor[] pds = src.getPropertyDescriptors();

        Set<String> emptyNames = new HashSet<>();
        for (PropertyDescriptor pd : pds) {
            Object srcValue = src.getPropertyValue(pd.getName());
            if (srcValue == null) {
                emptyNames.add(pd.getName());
            }
        }
        return emptyNames.toArray(new String[0]);
    }
}